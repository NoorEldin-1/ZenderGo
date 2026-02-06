<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    /**
     * Handle an incoming request.
     * Checks subscription status directly for instant protection.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Check if suspended for SECURITY reason - completely blocked
        if ($user->is_suspended && $user->suspension_reason === 'security') {
            return redirect()->route('subscription.locked');
        }

        // Check if suspended for SUBSCRIPTION reason - redirect to subscription page
        // This takes priority over hasActiveSubscription() because admin explicitly suspended them
        if ($user->is_suspended && $user->suspension_reason === 'subscription') {
            return redirect()->route('subscription.index');
        }

        // Check subscription status - redirect to subscription page to renew
        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscription.index');
        }

        return $next($request);
    }
}
