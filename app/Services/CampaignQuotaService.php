<?php

namespace App\Services;

use App\Models\CampaignQuota;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CampaignQuotaService
{
    /**
     * Cache TTL in seconds (5 hours default).
     */
    protected int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = SystemSetting::getCampaignQuotaWindowHours() * 3600;
    }

    /**
     * Get cache key for user quota.
     */
    protected function getCacheKey(int $userId): string
    {
        return "campaign_quota:{$userId}";
    }

    /**
     * Get or create quota record for user.
     */
    public function getQuota(User $user): CampaignQuota
    {
        $cacheKey = $this->getCacheKey($user->id);

        // Try to get from cache first
        $quota = Cache::get($cacheKey);

        if ($quota instanceof CampaignQuota) {
            // Refresh from DB if window might be expired
            if ($quota->isWindowExpired()) {
                Cache::forget($cacheKey);
                $quota = null;
            }
        } else {
            $quota = null;
        }

        if (!$quota) {
            $quota = CampaignQuota::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'contacts_sent' => 0,
                    'window_starts_at' => now(),
                    'window_ends_at' => now()->addHours(SystemSetting::getCampaignQuotaWindowHours()),
                ]
            );

            // If window is expired, reset it
            if ($quota->isWindowExpired()) {
                $quota->resetWindow();
                $quota->refresh();
            }

            // Cache the quota
            Cache::put($cacheKey, $quota, $this->cacheTtl);
        }

        return $quota;
    }

    /**
     * Check if user can send to N contacts.
     */
    public function canSend(User $user, int $contactCount): bool
    {
        $quota = $this->getQuota($user);
        return $quota->canSendTo($contactCount);
    }

    /**
     * Record usage after successful send.
     */
    public function recordUsage(User $user, int $contactCount): void
    {
        $quota = $this->getQuota($user);

        // If window expired, reset first
        if ($quota->isWindowExpired()) {
            $quota->resetWindow();
        }

        $quota->recordUsage($contactCount);

        // Refresh and update cache
        $quota->refresh();
        Cache::put($this->getCacheKey($user->id), $quota, $this->cacheTtl);

        Log::info("Campaign quota updated for user {$user->id}: sent {$contactCount}, total {$quota->contacts_sent}");
    }

    /**
     * Get current quota status for UI display.
     */
    public function getQuotaStatus(User $user): array
    {
        $quota = $this->getQuota($user);
        $limit = SystemSetting::getCampaignQuotaLimit();
        $remaining = $quota->getRemainingQuota();
        $resetInfo = $quota->getTimeUntilReset();

        return [
            'limit' => $limit,
            'used' => $quota->contacts_sent,
            'remaining' => $remaining,
            'percentage_used' => $quota->getUsagePercentage(),
            'percentage_remaining' => 100 - $quota->getUsagePercentage(),
            'status_color' => $quota->getStatusColor(),
            'reset_in' => $resetInfo['formatted'],
            'reset_hours' => $resetInfo['hours'],
            'reset_minutes' => $resetInfo['minutes'],
            'is_window_expired' => $resetInfo['is_expired'],
            'window_starts_at' => $quota->window_starts_at?->toIso8601String(),
            'window_ends_at' => $quota->window_ends_at?->toIso8601String(),
        ];
    }

    /**
     * Force refresh quota from database (bypass cache).
     */
    public function refreshQuota(User $user): CampaignQuota
    {
        Cache::forget($this->getCacheKey($user->id));
        return $this->getQuota($user);
    }
}
