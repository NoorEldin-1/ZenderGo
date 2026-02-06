<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSuspension
{
    /**
     * Handle an incoming request.
     * Check if the authenticated user is suspended and force logout if so.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_suspended) {
            $reason = Auth::user()->suspension_reason;

            // Strict block for security reasons
            if ($reason === 'security') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('suspension_error', 'الحساب معطل لسبب أمني');
            }

            // Subscription suspensions are allowed to login, but will be restricted by EnsureSubscriptionActive middleware on specific routes
        }

        return $next($request);
    }
}
