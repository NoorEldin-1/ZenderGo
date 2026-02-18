<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareRequestContact extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'share_request_id',
        'contact_id',
        'name',
        'phone',
    ];

    /**
     * Get the share request this contact belongs to.
     */
    public function shareRequest(): BelongsTo
    {
        return $this->belongsTo(ShareRequest::class);
    }

    /**
     * Get the original contact (if still exists).
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
