<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SessionManager;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     * Check password, then user exists and their WhatsApp connection status.
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
            'password' => 'required|string|min:6',
        ]);

        $phone = $request->phone;

        // Find user by phone
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            // User doesn't exist, redirect to registration
            return redirect()->route('register')
                ->with('info', 'رقم الهاتف غير مسجل. يرجى التسجيل أولاً.');
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withInput()
                ->withErrors(['password' => 'كلمة المرور غير صحيحة']);
        }

        // Check if user is suspended for SECURITY reason - completely block
        // Subscription suspended users CAN login (will be redirected to subscription page)
        if ($user->is_suspended && $user->suspension_reason === 'security') {
            return back()
                ->withInput()
                ->withErrors(['phone' => $user->suspension_reason_text]);
        }

        // User exists and has WhatsApp session configured - allow login
        // No need to check real-time connection since:
        // 1. Session might be "sleeping" (closed for RAM but still valid)
        // 2. Connection will be checked when user sends a campaign
        if ($user->whatsapp_session && $user->whatsapp_token) {
            // User has WhatsApp configured, log them in
            // Set state to sleeping (session will wake when needed)
            if (!$user->session_state || $user->session_state === 'none') {
                $user->update(['session_state' => 'sleeping']);
            }
            Auth::login($user, true);
            return redirect()->intended(route('contacts.index'));
        }

        // User exists but no WhatsApp session - redirect to reconnect
        session(['login_phone' => $phone, 'login_user_id' => $user->id]);
        return redirect()->route('login.reconnect')
            ->with('warning', 'يرجى ربط حساب WhatsApp الخاص بك.');
    }


    /**
     * Show the reconnect page for existing users with expired sessions.
     * Supports both:
     * - Users redirected from login (session-based)
     * - Already authenticated users with disconnected WhatsApp
     */
    public function showReconnectForm()
    {
        // Check for session-based flow (from login)
        $sessionUserId = session('login_user_id');

        // Also support already logged-in users
        $authUserId = Auth::id();

        $userId = $sessionUserId ?? $authUserId;

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        // Store session data for reconnect flow if coming from authenticated user
        if (!$sessionUserId && $authUserId) {
            session([
                'login_phone' => $user->phone,
                'login_user_id' => $user->id,
            ]);
        }

        return view('auth.reconnect', [
            'phone' => $user->phone,
            'isLoggedIn' => (bool) $authUserId,
        ]);
    }

    /**
     * Start reconnection session for existing user (AJAX).
     * Supports both session-based (from login) and authenticated users.
     * Supports both QR code and pairing code methods.
     * 
     * CLEAN SLATE APPROACH: Force logout old session before starting new one.
     * This ensures no stale tokens/browser data conflict with new connection.
     */
    public function startReconnect(Request $request)
    {
        // Validate optional method parameter
        $request->validate([
            'method' => 'nullable|in:qr,code',
        ]);

        $method = $request->input('method', 'code');

        // Support both session-based and authenticated users
        $userId = session('login_user_id') ?? Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'الجلسة منتهية. يرجى تسجيل الدخول مرة أخرى.',
            ], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود.',
            ], 404);
        }

        // Use STATIC session name for consistency
        $stableSessionName = 'user-' . $user->id;

        Log::info("Starting CLEAN SLATE reconnect for user {$user->id}, method: {$method}");

        // ============================================================
        // CRITICAL: CLEAN SLATE RECONNECT
        // Step 1: Close browser to free RAM
        // Step 2: Force logout to delete all stale data (tokens, userDataDir)
        // ============================================================
        if ($user->whatsapp_session && $user->whatsapp_token) {
            Log::info("Cleaning up old session before reconnect for user {$user->id}: {$user->whatsapp_session}");

            $oldWhatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);

            // Step 1: Close browser first (free RAM immediately)
            $oldWhatsapp->closeSession();
            Log::info("Closed browser for user {$user->id} to free RAM");

            // Step 2: Force logout to delete stale tokens and browser data files
            $oldWhatsapp->forceLogoutSession();
            Log::info("Force logout completed for user {$user->id}");

            // Small delay to ensure Node.js cleanup completes
            usleep(500000); // 0.5 seconds
        }

        // Clear old token - we MUST generate a fresh one
        $user->update([
            'whatsapp_session' => $stableSessionName,
            'whatsapp_token' => null,
            'session_state' => 'reconnecting'
        ]);

        Log::info("Creating fresh session for user {$user->id}: {$stableSessionName}");

        // Create new WhatsApp service with NO token (will generate fresh one)
        $whatsapp = new WhatsAppService($stableSessionName);

        // ALWAYS generate new token during reconnect
        $tokenResult = $whatsapp->generateToken();
        if (!empty($tokenResult['token'])) {
            $user->whatsapp_token = $tokenResult['token'];
            $user->save();
            Log::info("Generated fresh token for user {$user->id}");
        } else {
            Log::error("Failed to generate token for user {$user->id}");
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء جلسة جديدة. يرجى المحاولة مرة أخرى.',
            ]);
        }

        // Handle pairing code method
        if ($method === 'code') {
            Log::info("Requesting pairing code for reconnect user {$user->id}");

            $pairingResult = $whatsapp->requestPairingCode($user->phone);

            if (!empty($pairingResult['pairingCode'])) {
                return response()->json([
                    'success' => true,
                    'status' => 'pairing_code',
                    'pairingCode' => $pairingResult['pairingCode'],
                    'message' => 'تم إنشاء كود الربط بنجاح. أدخل الكود في تطبيق WhatsApp.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $pairingResult['message'] ?? 'فشل في إنشاء كود الربط',
            ]);
        }

        // QR Code method (default)
        // Start session
        $result = $whatsapp->startSession();

        Log::info("Reconnect session result for user {$user->id}", $result);

        // If we got a QR code, return it
        if (!empty($result['qrcode'])) {
            return response()->json($result);
        }

        // If already connected, great!
        if (in_array($result['status'] ?? '', ['CONNECTED', 'isLogged', 'inChat'])) {
            Auth::login($user, true);
            session()->forget(['login_phone', 'login_user_id']);
            return response()->json([
                'success' => true,
                'status' => 'CONNECTED',
                'redirect' => route('contacts.index'),
            ]);
        }

        // Poll for QR code - Baileys might take a few seconds to generate it
        // Try up to 5 times with 2-second intervals
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            usleep(2000000); // Wait 2 seconds

            Log::info("Polling for QR code, attempt {$attempt}/5 for user {$user->id}");

            $qrResult = $whatsapp->getQrCode();
            if (!empty($qrResult['qrcode'])) {
                Log::info("QR code obtained on attempt {$attempt} for user {$user->id}");
                return response()->json([
                    'success' => true,
                    'status' => 'qrcode',
                    'qrcode' => $qrResult['qrcode'],
                ]);
            }

            // Also check if connected during polling
            $connectionCheck = $whatsapp->checkConnection();
            if ($connectionCheck['connected'] ?? false) {
                Auth::login($user, true);
                session()->forget(['login_phone', 'login_user_id']);
                return response()->json([
                    'success' => true,
                    'status' => 'CONNECTED',
                    'redirect' => route('contacts.index'),
                ]);
            }
        }

        // If still no QR after 5 attempts, return with retry message
        Log::warning("Failed to get QR code after 5 attempts for user {$user->id}");
        return response()->json([
            'success' => false,
            'status' => 'initializing',
            'message' => 'جاري بدء الجلسة، يرجى المحاولة مرة أخرى...',
        ]);
    }

    /**
     * Check reconnection status (AJAX).
     * Supports both session-based and authenticated users.
     */
    public function checkReconnect()
    {
        // Support both session-based and authenticated users
        $userId = session('login_user_id') ?? Auth::id();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'connected' => false,
            ]);
        }

        $user = User::find($userId);
        if (!$user || !$user->whatsapp_session) {
            return response()->json([
                'success' => false,
                'connected' => false,
            ]);
        }

        $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
        $status = $whatsapp->checkConnection();

        $statusMessage = strtoupper($status['status'] ?? '');

        // CRITICAL FIX: Only CONNECTED means truly connected
        // PAIRED means token file exists but doesn't guarantee valid connection
        // After mobile logout, tokens are invalid even if file exists!
        if ($statusMessage === 'CONNECTED') {
            // Actually connected with browser open - SUCCESS!
            if (!Auth::check()) {
                Auth::login($user, true);
            }

            // Close session to save RAM
            $sessionManager = new SessionManager();
            $sessionManager->closeSession($user);
            $user->update(['session_state' => 'sleeping']);

            session()->forget(['login_phone', 'login_user_id']);
            $redirectUrl = session()->pull('url.intended', route('contacts.index'));

            return response()->json([
                'success' => true,
                'connected' => true,
                'redirect' => $redirectUrl,
            ]);
        }

        // PAIRED or anything else = NOT truly connected, keep waiting
        return response()->json([
            'success' => true,
            'connected' => false,
            'status' => $statusMessage,
        ]);
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Start registration session (AJAX).
     * Validates password and generates a new session with QR code or pairing code.
     */
    public function startRegistration(Request $request)
    {
        // Validate password and optional method/phone
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
            'method' => 'nullable|in:qr,code',
            'phone' => 'nullable|string|min:10|max:20',
        ]);

        $method = $request->input('method', 'code');
        $phone = $request->input('phone');

        // For pairing code method, phone is required
        if ($method === 'code' && empty($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف مطلوب للربط عن طريق الكود',
            ], 400);
        }

        // Generate unique session name for registration
        $sessionName = 'reg-' . time() . '-' . rand(1000, 9999);

        Log::info("Starting registration session: {$sessionName}, method: {$method}");

        $whatsapp = new WhatsAppService($sessionName);

        // Generate token for this session
        $tokenResult = $whatsapp->generateToken();
        $token = $tokenResult['token'] ?? null;

        Log::info("Token generation result for registration", $tokenResult);

        // Store session info and hashed password in HTTP session for later
        session([
            'reg_session' => $sessionName,
            'reg_token' => $token,
            'reg_password' => Hash::make($request->password),
            'reg_phone' => $phone, // Store phone for pairing code flow
        ]);

        // Handle pairing code method
        if ($method === 'code') {
            Log::info("Requesting pairing code for registration session {$sessionName}");

            $pairingResult = $whatsapp->requestPairingCode($phone);

            if (!empty($pairingResult['pairingCode'])) {
                return response()->json([
                    'success' => true,
                    'status' => 'pairing_code',
                    'pairingCode' => $pairingResult['pairingCode'],
                    'message' => 'تم إنشاء كود الربط بنجاح. أدخل الكود في تطبيق WhatsApp.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $pairingResult['message'] ?? 'فشل في إنشاء كود الربط',
            ]);
        }

        // QR Code method (default)
        // Start the WhatsApp session
        $result = $whatsapp->startSession();

        Log::info("Start session result for registration", $result);

        // If we got a QR code, return it
        if (!empty($result['qrcode'])) {
            return response()->json($result);
        }

        // Poll for QR code - Baileys might take a few seconds to generate it
        // Try up to 10 times with 3-second intervals (30 seconds total)
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            usleep(3000000); // Wait 3 seconds

            Log::info("Polling for QR code, attempt {$attempt}/10 for registration session {$sessionName}");

            $qrResult = $whatsapp->getQrCode();
            if (!empty($qrResult['qrcode'])) {
                Log::info("QR code obtained on attempt {$attempt} for registration session {$sessionName}");
                return response()->json([
                    'success' => true,
                    'status' => 'qrcode',
                    'qrcode' => $qrResult['qrcode'],
                ]);
            }
        }

        // If still no QR after 10 attempts, return with retry message
        Log::warning("Failed to get QR code after 10 attempts for registration session {$sessionName}");
        return response()->json([
            'success' => false,
            'status' => 'initializing',
            'message' => 'جاري بدء الجلسة، يرجى المحاولة مرة أخرى...',
        ]);
    }

    /**
     * Check registration status (AJAX).
     * Checks if WhatsApp is connected and creates user account with password.
     */
    public function checkRegistration()
    {
        Log::emergency("CHECK-REG: Entering checkRegistration function.");
        $sessionName = session('reg_session');
        $token = session('reg_token');
        Log::emergency("CHECK-REG: Using token: '{$token}' for session: '{$sessionName}'");
        $hashedPassword = session('reg_password');

        if (!$sessionName) {
            Log::emergency("CHECK-REG: No active session found.");
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'لا توجد جلسة تسجيل نشطة',
            ]);
        }

        // ... (existing code for connection check) ...


        $whatsapp = new WhatsAppService($sessionName, $token);
        $status = $whatsapp->checkConnection();

        Log::emergency("CHECK-REG: Status for {$sessionName}: " . json_encode($status));

        Log::info("Registration check for session {$sessionName}", $status);

        if ($status['connected'] ?? false) {
            // Connected! Get the phone number from WhatsApp
            $phoneNumber = $this->getConnectedPhoneNumber($whatsapp, $sessionName);
            Log::emergency("CHECK-REG: Phone number result: " . ($phoneNumber ?? 'NULL'));

            if (!$phoneNumber) {
                // If we are connected but can't get the phone number yet, keep polling
                // Do NOT return connected: true, otherwise the frontend stops polling and freezes
                Log::info("Session connected but phone number missing for {$sessionName}, continuing to poll...");
                Log::emergency("CHECK-REG: Connected but NO PHONE. Returning connected=false to force poll.");
                return response()->json([
                    'success' => true,
                    'connected' => false, // Force polling retry
                    'message' => 'Waiting for phone number...',
                ]);
            }

            Log::emergency("CHECK-REG: Success with phone {$phoneNumber} - Proceeding to login/create");

            Log::emergency("CHECK-REG: Success with phone {$phoneNumber} - Proceeding to login/create");

            try {
                // Check if user already exists with this phone
                $existingUser = User::where('phone', $phoneNumber)->first();

                if ($existingUser) {
                    Log::emergency("CHECK-REG: Found existing user ID: {$existingUser->id}");
                    // Keep the registration session name - tokens are stored under this name
                    // Don't change to user-X as it would break the token lookup
                    $existingUser->whatsapp_session = $sessionName;
                    $existingUser->whatsapp_token = $token;
                    $existingUser->session_state = 'sleeping'; // Will wake on campaign send
                    // Update password if provided (re-registration updates password)
                    if ($hashedPassword) {
                        $existingUser->password = $hashedPassword;
                    }
                    $existingUser->save();
                    Log::emergency("CHECK-REG: Updated existing user.");

                    // CRITICAL: Close session after successful registration to save RAM
                    $sessionManager = new SessionManager();
                    $sessionManager->closeSession($existingUser);

                    Auth::login($existingUser, true);
                    session()->forget(['reg_session', 'reg_token', 'reg_password']);

                    return response()->json([
                        'success' => true,
                        'connected' => true,
                        'redirect' => route('contacts.index'),
                        'message' => 'مرحباً بعودتك! تم تحديث جلسة WhatsApp وكلمة المرور.',
                    ]);
                }

                Log::emergency("CHECK-REG: Creating NEW user");
                // Create new user with password - keep the registration session name
                // The tokens are stored under this session name in the Node.js server
                $user = User::create([
                    'name' => 'User ' . substr($phoneNumber, -4),
                    'phone' => $phoneNumber,
                    'password' => $hashedPassword,
                    'whatsapp_session' => $sessionName, // Keep registration session name
                    'whatsapp_token' => $token,
                    'session_state' => 'sleeping', // Will wake on campaign send
                ]);
                Log::emergency("CHECK-REG: Created new user ID: {$user->id}");

                // Create trial subscription for new user
                $user->createTrialSubscription();

                // CRITICAL: Close session after successful registration to save RAM
                $sessionManager = new SessionManager();
                $sessionManager->closeSession($user);

                Auth::login($user, true);
                session()->forget(['reg_session', 'reg_token', 'reg_password']);

                return response()->json([
                    'success' => true,
                    'connected' => true,
                    'redirect' => route('contacts.index'),
                    'message' => 'تم إنشاء حسابك وربطه بـ WhatsApp بنجاح!',
                ]);

            } catch (\Exception $e) {
                Log::emergency("CHECK-REG: DATABASE ERROR: " . $e->getMessage());
                Log::emergency("CHECK-REG: Trace: " . $e->getTraceAsString());

                return response()->json([
                    'success' => false,
                    'connected' => true, // Still connected, but DB failed
                    'message' => 'حدث خطأ في قاعدة البيانات: ' . $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'connected' => false,
        ]);
    }

    /**
     * Get the connected phone number from WhatsApp session.
     */
    private function getConnectedPhoneNumber(WhatsAppService $whatsapp, string $session): ?string
    {
        $baseUrl = config('services.whatsapp.url');
        $token = $whatsapp->getToken();

        // Retry logic to handle race conditions where Node.js hasn't saved the phone yet
        $maxRetries = 5;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {

            // Method 0: Try get-stored-phone endpoint (works even after browser closes)
            try {
                $response = \Illuminate\Support\Facades\Http::withToken($token)
                    ->timeout(10)
                    ->get("{$baseUrl}/api/{$session}/get-stored-phone");

                if ($response->successful()) {
                    $data = $response->json();

                    if ($attempt > 1 || !empty($data['response']['phoneNumber'])) {
                        Log::info("get-stored-phone response for {$session} (Attempt {$attempt})", $data);
                    }

                    $phone = $data['response']['phoneNumber'] ?? null;

                    if ($phone) {
                        return $this->formatPhoneNumber($phone);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("get-stored-phone failed (Attempt {$attempt}): " . $e->getMessage());
            }

            // Method 1: Try get-phone-number endpoint (direct from browser)
            try {
                $response = \Illuminate\Support\Facades\Http::withToken($token)
                    ->timeout(10)
                    ->get("{$baseUrl}/api/{$session}/get-phone-number");

                if ($response->successful()) {
                    $data = $response->json();
                    $phone = $data['response']['phoneNumber']
                        ?? $data['response']
                        ?? $data['phoneNumber']
                        ?? null;

                    if ($phone) {
                        return $this->formatPhoneNumber($phone);
                    }
                }
            } catch (\Exception $e) {
                // suppress log for method 1
            }

            // If not found, wait and retry
            if ($attempt < $maxRetries) {
                sleep(1);
            }
        }

        // Method 2: Try host-device endpoint (fallback)
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(10)
                ->get("{$baseUrl}/api/{$session}/host-device");

            if ($response->successful()) {
                $data = $response->json();
                $phone = $data['response']['id']['user']
                    ?? $data['response']['wid']['user']
                    ?? $data['id']['user']
                    ?? null;

                if ($phone) {
                    return $this->formatPhoneNumber($phone);
                }
            }
        } catch (\Exception $e) {
            Log::warning("host-device failed: " . $e->getMessage());
        }

        Log::error("All methods failed to get phone number for session {$session} after {$maxRetries} attempts");
        return null;
    }

    /**
     * Format phone number to local format.
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters (handles formats like "201234567890@c.us")
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert from international (20...) to local (0...) format
        if (str_starts_with($phone, '20') && strlen($phone) > 10) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }

    /**
     * Log out user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the change password form.
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle password change request.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة']);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح!');
    }
}
