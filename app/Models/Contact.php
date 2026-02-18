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
        'last_sent_at',
        'is_featured',
        'label_text',
        'label_color',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_sent_at' => 'datetime',
        'is_featured' => 'boolean',
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
