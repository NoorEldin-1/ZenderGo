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
            return redirect()->intended(route('guide'));
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
     */
    public function startReconnect()
    {
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

        // Use existing session name or generate new one
        if (!$user->whatsapp_session) {
            $user->whatsapp_session = 'user-' . $user->id;
            $user->save();
        }

        Log::info("Starting reconnect session for user {$user->id}: {$user->whatsapp_session}");

        $whatsapp = new WhatsAppService($user->whatsapp_session);

        // Generate token
        $tokenResult = $whatsapp->generateToken();
        if (!empty($tokenResult['token'])) {
            $user->whatsapp_token = $tokenResult['token'];
            $user->save();
        }

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
                'redirect' => route('guide'),
            ]);
        }

        // Try to get QR code separately
        usleep(1000000);
        $qrResult = $whatsapp->getQrCode();
        if (!empty($qrResult['qrcode'])) {
            $result['qrcode'] = $qrResult['qrcode'];
            $result['success'] = true;
        }

        return response()->json($result);
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

        if ($status['connected'] ?? false) {
            // Connected! Log in the user if not already
            if (!Auth::check()) {
                Auth::login($user, true);
            }

            // CRITICAL: Close session after successful reconnect to save RAM
            // Session will wake when user sends a campaign
            $sessionManager = new SessionManager();
            $sessionManager->closeSession($user);

            // Set session state to sleeping (will wake on campaign send)
            $user->update(['session_state' => 'sleeping']);

            session()->forget(['login_phone', 'login_user_id']);

            // Redirect to intended URL or guide
            $redirectUrl = session()->pull('url.intended', route('guide'));

            return response()->json([
                'success' => true,
                'connected' => true,
                'redirect' => $redirectUrl,
            ]);
        }

        return response()->json([
            'success' => true,
            'connected' => false,
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
     * Validates password and generates a new session with QR code.
     */
    public function startRegistration(Request $request)
    {
        // Validate password
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Generate unique session name for registration
        $sessionName = 'reg-' . time() . '-' . rand(1000, 9999);

        Log::info("Starting registration session: {$sessionName}");

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
        ]);

        // Start the WhatsApp session
        $result = $whatsapp->startSession();

        Log::info("Start session result for registration", $result);

        // If we got a QR code, return it
        if (!empty($result['qrcode'])) {
            return response()->json($result);
        }

        // Try to get QR code separately
        usleep(1000000);
        $qrResult = $whatsapp->getQrCode();
        if (!empty($qrResult['qrcode'])) {
            $result['qrcode'] = $qrResult['qrcode'];
            $result['success'] = true;
        }

        return response()->json($result);
    }

    /**
     * Check registration status (AJAX).
     * Checks if WhatsApp is connected and creates user account with password.
     */
    public function checkRegistration()
    {
        $sessionName = session('reg_session');
        $token = session('reg_token');
        $hashedPassword = session('reg_password');

        if (!$sessionName) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'لا توجد جلسة تسجيل نشطة',
            ]);
        }

        $whatsapp = new WhatsAppService($sessionName, $token);
        $status = $whatsapp->checkConnection();

        Log::info("Registration check for session {$sessionName}", $status);

        if ($status['connected'] ?? false) {
            // Connected! Get the phone number from WhatsApp
            $phoneNumber = $this->getConnectedPhoneNumber($whatsapp, $sessionName);

            if (!$phoneNumber) {
                return response()->json([
                    'success' => false,
                    'connected' => true,
                    'message' => 'تم الربط ولكن لم نتمكن من الحصول على رقم الهاتف. يرجى المحاولة مرة أخرى.',
                ]);
            }

            // Check if user already exists with this phone
            $existingUser = User::where('phone', $phoneNumber)->first();
            if ($existingUser) {
                // Update existing user's session info and optionally password, then log them in
                $existingUser->whatsapp_session = $sessionName;
                $existingUser->whatsapp_token = $token;
                $existingUser->session_state = 'sleeping'; // Will wake on campaign send
                // Update password if provided (re-registration updates password)
                if ($hashedPassword) {
                    $existingUser->password = $hashedPassword;
                }
                $existingUser->save();

                // CRITICAL: Close session after successful registration to save RAM
                $sessionManager = new SessionManager();
                $sessionManager->closeSession($existingUser);

                Auth::login($existingUser, true);
                session()->forget(['reg_session', 'reg_token', 'reg_password']);

                return response()->json([
                    'success' => true,
                    'connected' => true,
                    'redirect' => route('guide'),
                    'message' => 'مرحباً بعودتك! تم تحديث جلسة WhatsApp وكلمة المرور.',
                ]);
            }

            // Create new user with password
            $user = User::create([
                'name' => 'User ' . substr($phoneNumber, -4),
                'phone' => $phoneNumber,
                'password' => $hashedPassword,
                'whatsapp_session' => $sessionName,
                'whatsapp_token' => $token,
                'session_state' => 'sleeping', // Will wake on campaign send
            ]);

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
                'redirect' => route('guide'),
                'message' => 'تم التسجيل بنجاح!',
            ]);
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

        // Method 1: Try get-phone-number endpoint (most direct)
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(10)
                ->get("{$baseUrl}/api/{$session}/get-phone-number");

            if ($response->successful()) {
                $data = $response->json();
                Log::info("get-phone-number response for {$session}", $data);

                $phone = $data['response']['phoneNumber']
                    ?? $data['response']
                    ?? $data['phoneNumber']
                    ?? null;

                if ($phone) {
                    return $this->formatPhoneNumber($phone);
                }
            }
        } catch (\Exception $e) {
            Log::warning("get-phone-number failed: " . $e->getMessage());
        }

        // Method 2: Try host-device endpoint (fallback)
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(10)
                ->get("{$baseUrl}/api/{$session}/host-device");

            if ($response->successful()) {
                $data = $response->json();
                Log::info("host-device response for {$session}", $data);

                // Try different possible response structures
                $phone = $data['response']['id']['user']
                    ?? $data['response']['wid']['user']
                    ?? $data['response']['id']['_serialized']
                    ?? $data['id']['user']
                    ?? $data['wid']['user']
                    ?? null;

                if ($phone) {
                    return $this->formatPhoneNumber($phone);
                }
            }
        } catch (\Exception $e) {
            Log::warning("host-device failed: " . $e->getMessage());
        }

        // Method 3: Try check-connection-session which might have user info
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(10)
                ->get("{$baseUrl}/api/{$session}/check-connection-session");

            if ($response->successful()) {
                $data = $response->json();
                Log::info("check-connection-session response for {$session}", $data);

                // Some versions include phone in connection response
                $phone = $data['response']['id']['user']
                    ?? $data['id']['user']
                    ?? null;

                if ($phone) {
                    return $this->formatPhoneNumber($phone);
                }
            }
        } catch (\Exception $e) {
            Log::warning("check-connection-session failed: " . $e->getMessage());
        }

        Log::error("All methods failed to get phone number for session {$session}");
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
