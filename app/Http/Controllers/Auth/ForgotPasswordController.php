<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SessionManager;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * OTP cache TTL in seconds (15 minutes).
     */
    private const OTP_TTL_SECONDS = 900;

    /**
     * Maximum OTP send attempts per phone per hour.
     */
    private const MAX_OTP_ATTEMPTS = 5;

    /**
     * Show the phone input form (Step 1).
     */
    public function showRequestForm()
    {
        $supportPhone = SystemSetting::getSupportPhoneNumber();

        return view('auth.passwords.request', compact('supportPhone'));
    }

    /**
     * Send OTP to user's WhatsApp (AJAX).
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:8|max:20',
        ]);

        $phone = preg_replace('/[^\d]/', '', $request->phone);

        // Check if user exists with this phone
        $targetUser = User::where('phone', $phone)->first();

        // Fallback for old Egyptian numbers (auto-migration support)
        if (!$targetUser && str_starts_with($phone, '201') && strlen($phone) == 12) {
            $fallbackPhone = '0' . substr($phone, 2);
            $targetUser = User::where('phone', $fallbackPhone)->first();
            if ($targetUser) {
                $phone = $targetUser->phone;
            }
        } elseif (!$targetUser && str_starts_with($phone, '01') && strlen($phone) == 11) {
            $fallbackPhone = '20' . substr($phone, 1);
            $targetUser = User::where('phone', $fallbackPhone)->first();
            if ($targetUser) {
                $phone = $targetUser->phone;
            }
        }

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد حساب مسجل بهذا الرقم.',
            ], 404);
        }

        // Rate limiting: max attempts per hour
        $rateLimitKey = "forgot_password_attempts:{$phone}";
        $attempts = (int) Cache::get($rateLimitKey, 0);

        if ($attempts >= self::MAX_OTP_ATTEMPTS) {
            return response()->json([
                'success' => false,
                'message' => 'لقد تجاوزت الحد الأقصى للمحاولات. حاول بعد ساعة.',
            ], 429);
        }

        // Find the support user account
        $supportPhone = SystemSetting::getSupportPhoneNumber();
        $supportUser = User::where('phone', $supportPhone)->first();

        if (!$supportUser || !$supportUser->whatsapp_session || !$supportUser->whatsapp_token) {
            Log::error('Forgot Password: Support user not found or not configured', [
                'support_phone' => $supportPhone,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'النظام قيد الصيانة حالياً. حاول مرة أخرى لاحقاً.',
            ], 503);
        }

        // Check support user's WhatsApp connection
        $sessionManager = new SessionManager();
        $wakeResult = $sessionManager->wakeSession($supportUser, 'web');

        if ($wakeResult['status'] !== 'connected' || !$wakeResult['service']) {
            Log::error('Forgot Password: Support WhatsApp not connected', [
                'status' => $wakeResult['status'],
                'message' => $wakeResult['message'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خدمة الرسائل غير متاحة حالياً. حاول مرة أخرى بعد قليل.',
            ], 503);
        }

        $whatsapp = $wakeResult['service'];

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache
        $otpKey = "password_reset_otp:{$phone}";
        Cache::put($otpKey, $otp, self::OTP_TTL_SECONDS);

        // Compose WhatsApp message
        $message = "🔐 *كود استعادة كلمة المرور*\n\n"
            . "كود التحقق الخاص بك هو:\n\n"
            . "▶️  *{$otp}*\n\n"
            . "⏳ الكود صالح لمدة *15 دقيقة* فقط.\n"
            . "⚠️ لا تشارك هذا الكود مع أي شخص.\n\n"
            . "إذا لم تطلب استعادة كلمة المرور، تجاهل هذه الرسالة.";

        // Send OTP via WhatsApp
        $sendResult = $whatsapp->sendMessageWithVerification($phone, $message);

        // Close session after sending to free RAM
        $sessionManager->closeSession($supportUser);

        if (!($sendResult['success'] ?? false)) {
            Log::error('Forgot Password: Failed to send OTP', [
                'phone' => $phone,
                'reason' => $sendResult['reason'] ?? 'unknown',
            ]);

            // Remove stored OTP on failure
            Cache::forget($otpKey);

            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال الكود. حاول مرة أخرى.',
            ], 500);
        }

        // Increment rate limit
        Cache::put($rateLimitKey, $attempts + 1, 3600);

        // Store phone in session for next steps
        session(['forgot_password_phone' => $phone]);

        Log::info('Forgot Password: OTP sent successfully', ['phone' => $phone]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال كود التحقق بنجاح!',
            'supportPhone' => $supportPhone,
        ]);
    }

    /**
     * Show the OTP verification form (Step 2).
     */
    public function showVerifyForm()
    {
        $phone = session('forgot_password_phone');

        if (!$phone) {
            return redirect()->route('password.request')
                ->with('error', 'يرجى إدخال رقم الهاتف أولاً.');
        }

        $supportPhone = SystemSetting::getSupportPhoneNumber();

        return view('auth.passwords.verify', compact('phone', 'supportPhone'));
    }

    /**
     * Verify OTP (AJAX).
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $phone = session('forgot_password_phone');

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'انتهت الجلسة. يرجى البدء من جديد.',
            ], 401);
        }

        $otpKey = "password_reset_otp:{$phone}";
        $storedOtp = Cache::get($otpKey);

        if (!$storedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'انتهت صلاحية الكود. اطلب كود جديد.',
            ], 410);
        }

        if ($request->otp !== $storedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'الكود غير صحيح. حاول مرة أخرى.',
            ], 422);
        }

        // OTP verified - mark session as verified and remove OTP
        Cache::forget($otpKey);
        session(['forgot_password_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق بنجاح!',
        ]);
    }

    /**
     * Show the reset password form (Step 3).
     */
    public function showResetForm()
    {
        $phone = session('forgot_password_phone');
        $verified = session('forgot_password_verified');

        if (!$phone || !$verified) {
            return redirect()->route('password.request')
                ->with('error', 'يرجى التحقق من الكود أولاً.');
        }

        return view('auth.passwords.reset', compact('phone'));
    }

    /**
     * Reset the password (POST).
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $phone = session('forgot_password_phone');
        $verified = session('forgot_password_verified');

        if (!$phone || !$verified) {
            return redirect()->route('password.request')
                ->with('error', 'انتهت الجلسة. يرجى البدء من جديد.');
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'لا يوجد حساب بهذا الرقم.');
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear session data
        session()->forget([
            'forgot_password_phone',
            'forgot_password_verified',
        ]);

        Log::info('Forgot Password: Password reset successfully', ['user_id' => $user->id]);

        return redirect()->route('login')
            ->with('success', 'تم تغيير كلمة المرور بنجاح! يمكنك تسجيل الدخول الآن.');
    }
}
