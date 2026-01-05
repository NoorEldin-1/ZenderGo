<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateShareRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'status',
    ];

    /**
     * Get the sender of this share request.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of this share request.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the template items included in this share request.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TemplateShareItem::class);
    }

    /**
     * Scope to get pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get requests for a specific user (as recipient).
     */
    public function scopeForRecipient($query, $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    /**
     * Scope to get requests sent by a specific user.
     */
    public function scopeFromSender($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
