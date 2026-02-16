<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================
// SCHEDULED TASKS FOR SERVER HEALTH
// Optimized for 8GB VPS handling 50-70 users
// ============================================

// Close idle WhatsApp sessions every 2 minutes to free RAM (aggressive cleanup)
Schedule::command('sessions:cleanup')->everyTwoMinutes()->name('session-cleanup');

// Kill orphaned Chrome processes every 5 minutes (handles crash scenarios)
Schedule::command('sessions:kill-zombies --max-age=300')->everyFiveMinutes()->name('zombie-cleanup');

// Monitor RAM usage every minute (auto-cleanup if critical)
Schedule::command('ram:check --auto-cleanup')->everyMinute()->name('ram-monitor');

// Detect and resolve stuck campaigns every 10 minutes
Schedule::command('campaigns:monitor-stuck --minutes=30')->everyTenMinutes()->name('stuck-campaign-monitor');

// Check expired subscriptions daily at midnight
Schedule::command('subscriptions:check-expired')->daily();

// Clear failed jobs older than 7 days (prevents table bloat)
Schedule::command('queue:flush')->weekly();

// Cleanup old storage files weekly (low-traffic hours)
Schedule::command('storage:cleanup')->weeklyOn(0, '4:00')->name('storage-cleanup');

// Prune old telescope/log entries if using telescope
// Schedule::command('telescope:prune --hours=48')->daily();

// Optimize database tables weekly (run on low-traffic hours)
Schedule::call(function () {
    DB::statement('OPTIMIZE TABLE contacts');
    DB::statement('OPTIMIZE TABLE jobs');
    DB::statement('OPTIMIZE TABLE sessions');
})->weeklyOn(0, '3:00')->name('database-optimization');

// Clear old cache keys monthly
Schedule::command('cache:clear')->monthly();

// Regenerate route and config cache after any config changes
Schedule::call(function () {
    Artisan::call('config:cache');
    Artisan::call('route:cache');
})->dailyAt('04:00')->name('cache-rebuild');
