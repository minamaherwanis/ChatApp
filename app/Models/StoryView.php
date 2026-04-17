<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryView extends Model
{
    protected $fillable = [
        'story_id',
        'user_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];


    public function story()
    {
        return $this->belongsTo(Story::class);
    }

  
      public function profile()
    {
        return $this->hasOne(Profile::class,'user_id','user_id');
    }
}
