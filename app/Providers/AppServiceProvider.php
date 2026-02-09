<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination views
        Paginator::useBootstrapFive();

        // Share theme preference with all views
        View::composer('*', function ($view) {
            $theme = 'light'; // Default for guests

            if (Auth::guard('admin')->check()) {
                $theme = Auth::guard('admin')->user()->theme_preference ?? 'light';
            } elseif (Auth::check()) {
                $theme = Auth::user()->theme_preference ?? 'light';
            }

            $view->with('currentTheme', $theme);
        });

        // Update last_login_at on user login
        Event::listen(Login::class, function (Login $event) {
            // Only update for the default 'web' guard (regular users)
            if ($event->guard === 'web' && $event->user instanceof \App\Models\User) {
                $event->user->update(['last_login_at' => now()]);
            }
        });
    }
}
