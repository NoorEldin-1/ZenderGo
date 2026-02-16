<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Statuses that indicate a campaign is still "in progress" (blocking new campaigns).
     */
    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
    ];

    protected $fillable = [
        'user_id',
        'total_contacts',
        'sent_count',
        'failed_count',
        'status',
        'failure_reason',
    ];

    protected $casts = [
        'total_contacts' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    /**
     * Get the user that owns the campaign.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this campaign is still considered "active" (blocking new ones).
     */
    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    /**
     * Check if this campaign has finished (regardless of success or failure).
     */
    public function isFinished(): bool
    {
        return !$this->isActive();
    }

    /**
     * Mark campaign as processing.
     */
    public function markProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Increment sent count and check if campaign is complete.
     */
    public function recordSuccess(): void
    {
        $this->increment('sent_count');
        $this->refresh();

        if (($this->sent_count + $this->failed_count) >= $this->total_contacts) {
            $this->update(['status' => self::STATUS_COMPLETED]);
        }
    }

    /**
     * Increment failed count and check if campaign is complete.
     */
    public function recordFailure(): void
    {
        $this->increment('failed_count');
        $this->refresh();

        if (($this->sent_count + $this->failed_count) >= $this->total_contacts) {
            // If everything failed, mark as failed; otherwise completed (partial success)
            $newStatus = $this->sent_count === 0
                ? self::STATUS_FAILED
                : self::STATUS_COMPLETED;

            $this->update(['status' => $newStatus]);
        }
    }

    /**
     * Mark campaign as failed with a reason.
     */
    public function markFailed(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Mark campaign as cancelled.
     */
    public function markCancelled(string $reason = 'تم الإلغاء'): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Get the active campaign for a specific user (if any).
     */
    public static function getActiveForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->latest()
            ->first();
    }

    /**
     * Get the latest campaign for a specific user (for status display).
     */
    public static function getLatestForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->latest()
            ->first();
    }
}
