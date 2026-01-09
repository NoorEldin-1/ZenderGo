<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'store_name',
        'last_sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the contact.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted last sent at timestamp.
     * Returns human-readable format like "2 hours ago" or "لم يتم التواصل" if never contacted.
     */
    public function getLastSentAtFormattedAttribute(): string
    {
        if (!$this->last_sent_at) {
            return 'لم يتم التواصل';
        }
        return $this->last_sent_at->diffForHumans();
    }
}
