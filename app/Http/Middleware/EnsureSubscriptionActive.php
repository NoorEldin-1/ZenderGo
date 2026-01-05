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

        // Check if suspended for subscription reason
        if ($user->is_suspended && $user->suspension_reason === 'subscription') {
            return redirect()->route('subscription.locked');
        }

        // Direct subscription check (instant protection without scheduler)
        if (!$user->hasActiveSubscription() && !$user->is_suspended) {
            $user->suspend('subscription');
            return redirect()->route('subscription.locked');
        }

        return $next($request);
    }
}
