<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateShareItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'template_share_request_id',
        'name',
        'content',
    ];

    /**
     * Get the share request this item belongs to.
     */
    public function shareRequest(): BelongsTo
    {
        return $this->belongsTo(TemplateShareRequest::class, 'template_share_request_id');
    }
}
