<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
        protected $fillable = [
        'phone',
        'code',
        'expires_at',
        'verified_at',
        'attempts',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_used' => 'boolean',
    ];

}
