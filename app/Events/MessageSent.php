<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

 
    public function __construct(public $messageId,public $userId)
    {
        
        //
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.'.$this->userId),
        ];
    }

    public function broadcastWith()
    {
        return [
            'messageId' => $this->messageId,
        ];
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
