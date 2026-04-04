<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
   protected $fillable = [
        'user_id',
        'username',
        'name',
        'avatar',
        'bio',
        'email',
        'is_online',
        'last_seen'
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen' => 'datetime',
    ];

    // relation مع user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
