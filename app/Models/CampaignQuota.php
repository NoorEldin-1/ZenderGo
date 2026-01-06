<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CampaignQuota extends Model
{
    protected $fillable = [
        'user_id',
        'contacts_sent',
        'window_starts_at',
        'window_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'contacts_sent' => 'integer',
            'window_starts_at' => 'datetime',
            'window_ends_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this quota.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the current window has expired.
     */
    public function isWindowExpired(): bool
    {
        // If no window set, it's considered expired (needs initialization)
        if (!$this->window_ends_at) {
            return true;
        }
        return $this->window_ends_at->isPast();
    }

    /**
     * Get remaining quota for current window.
     */
    public function getRemainingQuota(): int
    {
        if ($this->isWindowExpired()) {
            return SystemSetting::getCampaignQuotaLimit();
        }

        $limit = SystemSetting::getCampaignQuotaLimit();
        $remaining = $limit - $this->contacts_sent;

        return max(0, $remaining);
    }

    /**
     * Check if user can send to N contacts.
     */
    public function canSendTo(int $count): bool
    {
        return $this->getRemainingQuota() >= $count;
    }

    /**
     * Record usage and increment counter.
     */
    public function recordUsage(int $count): void
    {
        // If window expired, start a new one
        if ($this->isWindowExpired()) {
            $this->resetWindow();
        }

        $this->increment('contacts_sent', $count);
    }

    /**
     * Reset window and start fresh.
     */
    public function resetWindow(): void
    {
        $windowHours = SystemSetting::getCampaignQuotaWindowHours();

        $this->update([
            'contacts_sent' => 0,
            'window_starts_at' => now(),
            'window_ends_at' => now()->addHours($windowHours),
        ]);
    }

    /**
     * Get formatted time until quota reset.
     */
    public function getTimeUntilReset(): array
    {
        if ($this->isWindowExpired()) {
            return [
                'hours' => 0,
                'minutes' => 0,
                'formatted' => 'متاحة الآن',
                'is_expired' => true,
            ];
        }

        $now = now();
        $end = $this->window_ends_at;

        $totalMinutes = (int) $now->diffInMinutes($end, false);
        $hours = (int) floor($totalMinutes / 60);
        $minutes = (int) ($totalMinutes % 60);

        // Build formatted string
        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours} ساعة";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes} دقيقة";
        }

        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'formatted' => implode(' و ', $parts) ?: 'أقل من دقيقة',
            'is_expired' => false,
        ];
    }

    /**
     * Get usage percentage for progress bar.
     */
    public function getUsagePercentage(): int
    {
        if ($this->isWindowExpired()) {
            return 0;
        }

        $limit = SystemSetting::getCampaignQuotaLimit();
        if ($limit === 0) {
            return 100;
        }

        return (int) round(($this->contacts_sent / $limit) * 100);
    }

    /**
     * Get status color based on usage.
     */
    public function getStatusColor(): string
    {
        $percentage = $this->getUsagePercentage();

        if ($percentage >= 100) {
            return 'danger';
        } elseif ($percentage >= 90) {
            return 'danger';
        } elseif ($percentage >= 75) {
            return 'warning';
        } else {
            return 'success';
        }
    }
}
