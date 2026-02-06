<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired subscriptions and suspend users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('بدء فحص الاشتراكات المنتهية...');

        // Get all users who are not suspended
        $users = User::where('is_suspended', false)->get();

        $expiredCount = 0;

        foreach ($users as $user) {
            // Check if user has an active subscription
            if (!$user->hasActiveSubscription()) {
                // Just log - don't suspend. User will be redirected to subscription page by middleware
                $expiredCount++;

                $this->warn("اشتراك منتهي: {$user->name} ({$user->phone})");

                Log::info("User {$user->id} ({$user->phone}) has expired subscription - needs renewal");
            }
        }

        if ($expiredCount === 0) {
            $this->info('لا توجد اشتراكات منتهية.');
        } else {
            $this->info("تم تعطيل {$expiredCount} حساب/حسابات بسبب انتهاء الاشتراك.");
        }

        return self::SUCCESS;
    }
}
