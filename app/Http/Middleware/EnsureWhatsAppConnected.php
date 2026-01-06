<?php

namespace App\Http\Middleware;

use App\Services\WhatsAppService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure WhatsApp session is connected before allowing operations.
 * When disconnection is detected, the user is logged out completely and
 * redirected to login page to re-authenticate and reconnect WhatsApp.
 * 
 * This middleware performs REAL-TIME checks on each request (no caching)
 * to ensure immediate detection of disconnection.
 */
class EnsureWhatsAppConnected
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Check if user has a WhatsApp session configured
        if (!$user->whatsapp_session) {
            return $this->handleDisconnected(
                $request,
                'لا توجد جلسة WhatsApp مرتبطة بحسابك. يرجى تسجيل الدخول مجدداً وربط WhatsApp.',
                $user
            );
        }

        // Real-time connection check (no caching to ensure immediate detection)
        $isConnected = $this->checkConnectionStatus($user);

        if (!$isConnected) {
            return $this->handleDisconnected(
                $request,
                'تم قطع اتصال WhatsApp. يرجى تسجيل الدخول مجدداً وربط WhatsApp.',
                $user
            );
        }

        return $next($request);
    }

    /**
     * Check the actual WhatsApp connection status via API.
     */
    protected function checkConnectionStatus($user): bool
    {
        try {
            $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $status = $whatsapp->checkConnection();

            $connected = $status['connected'] ?? false;

            if (!$connected) {
                Log::info("WhatsApp session disconnected for user {$user->id}", [
                    'session' => $user->whatsapp_session,
                    'status' => $status,
                ]);
            }

            return $connected;
        } catch (\Exception $e) {
            Log::warning("Failed to check WhatsApp connection for user {$user->id}", [
                'error' => $e->getMessage(),
            ]);

            // On API error, allow the request to proceed
            // to avoid false lockouts due to temporary API issues
            return true;
        }
    }

    /**
     * Handle disconnected state - logout and redirect to login page.
     */
    protected function handleDisconnected(Request $request, string $message, $user): Response
    {
        Log::warning("WhatsApp disconnected - forcing logout", [
            'user_id' => $user->id,
            'route' => $request->route()?->getName(),
            'url' => $request->url(),
        ]);

        // For AJAX/API requests
        if ($request->expectsJson() || $request->ajax()) {
            // Logout the user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => false,
                'error' => 'whatsapp_disconnected',
                'message' => $message,
                'redirect' => route('login'),
            ], 401);
        }

        // Logout the user completely
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', $message);
    }
}
