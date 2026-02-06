<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'price_paid',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->ends_at->isFuture();
    }

    /**
     * Check if the subscription has expired.
     */
    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    /**
     * Get the number of days remaining in the subscription.
     */
    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return (int) now()->diffInDays($this->ends_at, false);
    }

    /**
     * Get detailed remaining time (days, hours, minutes) with formatted Arabic string.
     */
    public function detailedTimeRemaining(): array
    {
        if ($this->isExpired()) {
            return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'formatted' => 'منتهي'];
        }

        $now = now();
        $end = $this->ends_at;

        // Use Carbon's diff methods for better precision
        $diff = $now->diff($end);

        $days = $diff->days;
        $hours = $diff->h;
        $minutes = $diff->i;
        $seconds = $diff->s;

        // Build formatted string
        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days} يوم";
        }
        if ($hours > 0) {
            $parts[] = "{$hours} ساعة";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes} دقيقة";
        }
        // Only show seconds if less than 1 hour remaining
        if ($days == 0 && $hours == 0) {
            $parts[] = "{$seconds} ثانية";
        }

        return [
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'formatted' => implode('، ', $parts) ?: 'الآن'
        ];
    }

    /**
     * Get the percentage of subscription time remaining.
     */
    public function percentageRemaining(): int
    {
        $totalDays = $this->starts_at->diffInDays($this->ends_at);
        $remaining = $this->daysRemaining();

        if ($totalDays === 0) {
            return 0;
        }

        return (int) round(($remaining / $totalDays) * 100);
    }

    /**
     * Check if subscription is trial type.
     */
    public function isTrial(): bool
    {
        return $this->type === 'trial';
    }

    /**
     * Check if subscription is paid type.
     */
    public function isPaid(): bool
    {
        return $this->type === 'paid';
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('ends_at', '>', now());
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<=', now());
    }
}
