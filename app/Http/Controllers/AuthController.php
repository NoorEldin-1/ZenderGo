<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Check if user exists and their WhatsApp connection status.
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
        ]);

        $phone = $request->phone;

        // Find user by phone
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            // User doesn't exist, redirect to registration
            return redirect()->route('register')
                ->with('info', 'رقم الهاتف غير مسجل. يرجى التسجيل أولاً.');
        }

        // Check if user is suspended
        if ($user->is_suspended) {
            return back()
                ->withInput()
                ->withErrors(['phone' => $user->suspension_reason_text]);
        }

        // User exists, check if WhatsApp is connected
        if ($user->whatsapp_session) {
            $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $status = $whatsapp->checkConnection();

            if ($status['connected'] ?? false) {
                // User is connected, log them in
                Auth::login($user, true);
                return redirect()->intended(route('guide'));
            }
        }

        // User exists but not connected, store phone and redirect to reconnect
        session(['login_phone' => $phone, 'login_user_id' => $user->id]);
        return redirect()->route('login.reconnect')
            ->with('warning', 'جلسة WhatsApp منتهية. يرجى إعادة ربط حسابك.');
    }

    /**
     * Show the reconnect page for existing users with expired sessions.
     */
    public function showReconnectForm()
    {
        if (!session('login_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.reconnect', [
            'phone' => session('login_phone'),
        ]);
    }

    /**
     * Start reconnection session for existing user (AJAX).
     */
    public function startReconnect()
    {
        $userId = session('login_user_id');
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
     */
    public function checkReconnect()
    {
        $userId = session('login_user_id');
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
            // Connected! Log in the user
            Auth::login($user, true);
            session()->forget(['login_phone', 'login_user_id']);

            return response()->json([
                'success' => true,
                'connected' => true,
                'redirect' => route('guide'),
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
     * Generates a new session and returns QR code.
     */
    public function startRegistration()
    {
        // Generate unique session name for registration
        $sessionName = 'reg-' . time() . '-' . rand(1000, 9999);

        Log::info("Starting registration session: {$sessionName}");

        $whatsapp = new WhatsAppService($sessionName);

        // Generate token for this session
        $tokenResult = $whatsapp->generateToken();
        $token = $tokenResult['token'] ?? null;

        Log::info("Token generation result for registration", $tokenResult);

        // Store session info in HTTP session for later
        session([
            'reg_session' => $sessionName,
            'reg_token' => $token,
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
     * Checks if WhatsApp is connected and creates user account.
     */
    public function checkRegistration()
    {
        $sessionName = session('reg_session');
        $token = session('reg_token');

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
                // Update existing user's session info and log them in
                $existingUser->whatsapp_session = $sessionName;
                $existingUser->whatsapp_token = $token;
                $existingUser->save();

                Auth::login($existingUser, true);
                session()->forget(['reg_session', 'reg_token']);

                return response()->json([
                    'success' => true,
                    'connected' => true,
                    'redirect' => route('guide'),
                    'message' => 'مرحباً بعودتك! تم تحديث جلسة WhatsApp.',
                ]);
            }

            // Create new user
            $user = User::create([
                'name' => 'User ' . substr($phoneNumber, -4),
                'phone' => $phoneNumber,
                'whatsapp_session' => $sessionName,
                'whatsapp_token' => $token,
            ]);

            // Create trial subscription for new user
            $user->createTrialSubscription();

            Auth::login($user, true);
            session()->forget(['reg_session', 'reg_token']);

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
}
