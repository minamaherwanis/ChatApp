<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id', 'last_message_at'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];  
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }


    public static function findOrCreate($userOneId, $userTwoId)
    {
        $chat = self::where(function ($query) use ($userOneId, $userTwoId) {
            $query->where('user_one_id', $userOneId)
                  ->where('user_two_id', $userTwoId);
        })->orWhere(function ($query) use ($userOneId, $userTwoId) {
            $query->where('user_one_id', $userTwoId)
                  ->where('user_two_id', $userOneId);
        })->first();

        if (!$chat) {
            $chat = self::create([
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
                'last_message_at' => now(),

            ]);
        }

        return $chat;
    }


    public function getOtheruser()
    {
       return auth()->id() === $this->user_one_id ? $this->userTwo : $this->userOne;
    }

    public function lastMessage()
    {
        return $this->messages()->latest()->first();
    }

    public function getLastMessageFormattedAttribute()
    {
        $lastMessageAt = Carbon::parse($this->attributes['last_message_at']);
        
       if ($lastMessageAt->isToday()) {
            return $lastMessageAt->format('g:i A');
        } elseif ($lastMessageAt->isYesterday()) {
            return 'Yesterday';
        } else {
            return $lastMessageAt->format('M d, Y');
        }   
    }
    public function isChatContainerUser($userId)
    {
        return $this->user_one_id === $userId ||$this->user_two_id  === $userId ;
    }
}
