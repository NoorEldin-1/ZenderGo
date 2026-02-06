<?php

namespace App\Console\Commands;

use App\Models\CampaignQuota;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetUserQuota extends Command
{
    protected $signature = 'quota:reset {user_id? : The user ID to reset (leave empty for all users)}';
    protected $description = 'Nuclear reset: Clears all cache and DB quota records for a user';

    public function handle()
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            $this->resetSingleUser($userId);
        } else {
            if ($this->confirm('This will reset ALL users. Are you sure?')) {
                $this->resetAllUsers();
            }
        }

        return Command::SUCCESS;
    }

    protected function resetSingleUser($userId)
    {
        $this->info("========== NUCLEAR RESET FOR USER {$userId} ==========");

        // 1. Check and clear cache
        $cacheKey = "campaign_quota:{$userId}";
        $cachedValue = Cache::get($cacheKey);

        if ($cachedValue !== null) {
            $this->warn("Cache Key '{$cacheKey}' EXISTS!");
            if ($cachedValue instanceof CampaignQuota) {
                $this->line("  - Type: CampaignQuota");
                $this->line("  - contacts_sent: " . ($cachedValue->contacts_sent ?? 'NULL'));
                $this->line("  - window_ends_at: " . ($cachedValue->window_ends_at ?? 'NULL'));
            } else {
                $this->line("  - Type: " . gettype($cachedValue));
                $this->line("  - Value: " . json_encode($cachedValue));
            }
            Cache::forget($cacheKey);
            $this->info("  ✓ Cache CLEARED");
        } else {
            $this->info("Cache Key '{$cacheKey}' is already empty.");
        }

        // 2. Check and clear DB record
        $dbRecord = CampaignQuota::where('user_id', $userId)->first();
        if ($dbRecord) {
            $this->warn("DB Record EXISTS!");
            $this->line("  - id: {$dbRecord->id}");
            $this->line("  - contacts_sent: {$dbRecord->contacts_sent}");
            $this->line("  - window_ends_at: {$dbRecord->window_ends_at}");
            $dbRecord->delete();
            $this->info("  ✓ DB Record DELETED");
        } else {
            $this->info("DB Record does not exist.");
        }

        // 3. Clear any other potential cache keys
        $otherKeys = [
            "user:{$userId}:campaign_count",
            "user:{$userId}:messages_sent",
            "campaign_limit:{$userId}",
        ];
        foreach ($otherKeys as $key) {
            $value = Cache::get($key);
            if ($value !== null) {
                $this->warn("FOUND GHOST KEY: '{$key}' = " . json_encode($value));
                Cache::forget($key);
                $this->info("  ✓ Cleared");
            }
        }

        // 4. Check jobs table
        $pendingJobs = DB::table('jobs')
            ->where('payload', 'like', "%\"userId\":{$userId}%")
            ->orWhere('payload', 'like', "%user_id\":{$userId}%")
            ->count();
        $this->info("Pending Jobs in queue for this user: {$pendingJobs}");

        $failedJobs = DB::table('failed_jobs')
            ->where('payload', 'like', "%\"userId\":{$userId}%")
            ->orWhere('payload', 'like', "%user_id\":{$userId}%")
            ->count();
        $this->info("Failed Jobs for this user: {$failedJobs}");

        $this->newLine();
        $this->info("========== RESET COMPLETE ==========");
        Log::info("QUOTA NUCLEAR RESET executed for user {$userId}");
    }

    protected function resetAllUsers()
    {
        $this->info("========== NUCLEAR RESET FOR ALL USERS ==========");

        // Get all user IDs with quota records
        $userIds = CampaignQuota::pluck('user_id')->unique();

        // Clear cache for each
        foreach ($userIds as $userId) {
            Cache::forget("campaign_quota:{$userId}");
            $this->line("Cleared cache for user {$userId}");
        }

        // Also try to clear via pattern if using Redis
        try {
            $cacheStore = Cache::getStore();
            if (method_exists($cacheStore, 'flush')) {
                // If using file/array driver
                $this->warn("Consider running: php artisan cache:clear");
            }
        } catch (\Exception $e) {
            $this->error("Cache inspection error: " . $e->getMessage());
        }

        // Truncate the DB table
        $deletedCount = CampaignQuota::query()->delete();
        $this->info("Deleted {$deletedCount} records from campaign_quotas table.");

        $this->newLine();
        $this->info("========== ALL USERS RESET COMPLETE ==========");
        Log::info("QUOTA NUCLEAR RESET executed for ALL users");
    }
}
