<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['chat_id', 'sender_id', 'content', 'is_read'];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
    public function isSender()
    {
        return $this->sender_id === auth()->id();
    }


    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function getCreatedAtAttribute()
    {
        $lastMessageAt = Carbon::parse($this->attributes['created_at']);

        if ($lastMessageAt->isToday()) {
            return $lastMessageAt->format('g:i A');
        } elseif ($lastMessageAt->isYesterday()) {
            return 'Yesterday';
        } else {
            return $lastMessageAt->format('M d, Y');
        }
    }
}
