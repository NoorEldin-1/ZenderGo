<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    protected $fillable = ['name', 'content'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
