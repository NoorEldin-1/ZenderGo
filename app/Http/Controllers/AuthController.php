<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
     * Generate and send OTP via WhatsApp.
     */
    public function sendOtp(Request $request, WhatsAppService $whatsapp)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
        ]);

        $phone = $request->phone;

        // Find or create user by phone
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'User ' . Str::random(4)]
        );

        // Generate 4-digit OTP
        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        // Store OTP with expiration (5 minutes)
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        // Send OTP via WhatsApp
        $message = "Your verification code is: {$otp}\n\nThis code expires in 5 minutes.";
        $sent = $whatsapp->sendMessage($phone, $message);

        if (!$sent) {
            return back()->withErrors(['phone' => 'Failed to send OTP. Please try again.']);
        }

        // Store phone in session for verification step
        session(['otp_phone' => $phone]);

        return redirect()->route('verify')->with('success', 'OTP sent to your WhatsApp!');
    }

    /**
     * Show the OTP verification form.
     */
    public function showVerifyForm()
    {
        if (!session('otp_phone')) {
            return redirect()->route('login');
        }

        return view('auth.verify', [
            'phone' => session('otp_phone'),
        ]);
    }

    /**
     * Verify OTP and log in user.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:4',
        ]);

        $phone = session('otp_phone');

        if (!$phone) {
            return redirect()->route('login')->withErrors(['otp' => 'Session expired. Please try again.']);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'User not found.']);
        }

        // Check if OTP is valid and not expired
        if ($user->otp_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }

        if ($user->otp_expires_at && $user->otp_expires_at->isPast()) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        // Clear OTP
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Clear session
        session()->forget('otp_phone');

        // Log in user
        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
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
