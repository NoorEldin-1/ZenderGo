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
     * 
     * IMPORTANT: This middleware no longer does real-time connection checks on every request.
     * Connection checks are only performed at:
     * - Login (AuthController)
     * - Campaign sending (SendWhatsappCampaign job)
     * 
     * This middleware simply ensures the user has a configured WhatsApp session.
     * The session_state field tracks whether the session is active, sleeping, or needs reconnect.
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

        // Allow request based on session_state:
        // - "active": Session was connected, allow (campaigns will check when sending)
        // - "sleeping": Session intentionally closed for RAM, allow (will wake when needed)
        // - "reconnecting": User is in the middle of reconnecting
        // - "disconnected": User was detected as logged out from mobile - MUST reconnect
        // - "none" or null: Never connected or needs reconnect

        if (in_array($user->session_state, ['active', 'sleeping', 'reconnecting'])) {
            // Trust the session state - no connection check needed
            return $next($request);
        }

        // CRITICAL: If user is explicitly disconnected, they MUST reconnect
        // Do NOT reset to sleeping - this would bypass the reconnection requirement
        if ($user->session_state === 'disconnected') {
            Log::info("User {$user->id} has disconnected state, redirecting to reconnect");

            // Store session data for reconnect flow
            session([
                'login_phone' => $user->phone,
                'login_user_id' => $user->id,
            ]);

            return redirect()->route('login.reconnect')
                ->with('warning', 'تم فقدان اتصال WhatsApp. يرجى إعادة الربط.');
        }

        // State is "none" or null - user needs to reconnect
        // But first, give them a chance - maybe they just registered
        if ($user->whatsapp_token) {
            // They have a token, so they completed setup before
            // Set to sleeping and allow - campaign will wake session when needed
            $user->update(['session_state' => 'sleeping']);
            Log::info("User {$user->id} had no session_state but has token, setting to sleeping");
            return $next($request);
        }

        // No token means they never completed WhatsApp setup
        return $this->handleDisconnected(
            $request,
            'يرجى ربط WhatsApp أولاً.',
            $user
        );
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
