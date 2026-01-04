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
