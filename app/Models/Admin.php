<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the password attribute - convert $2a$ to $2y$ for Laravel compatibility.
     * Both are valid bcrypt hashes, just different prefixes.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Convert $2a$ to $2y$ when reading from database
                if ($value && str_starts_with($value, '$2a$')) {
                    return '$2y$' . substr($value, 4);
                }
                return $value;
            },
            set: function ($value) {
                // If it's already a bcrypt hash, store as-is
                if ($value && preg_match('/^\$2[aby]\$/', $value)) {
                    return $value;
                }
                // Otherwise, hash the plain password
                return Hash::make($value);
            },
        );
    }
}
