<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Profile;
use App\Models\User;
use Livewire\Component;

class Chat extends Component
{
    public $viewmode = 'chats';
    public $search = '';
    public $chats = [];

    public $messages = null;
    public $selectedChat = null;
    public $message = '';
    public $selectedUser = null;
    public $limit = 10, $height;



    public function selectChat($chatId)
    {
        $this->limit = 10;
        $chat = \App\Models\Chat::find($chatId);
        if (!$chat || !$chat->isChatContainerUser(auth()->id())) {
            return;
        }
        $this->selectedUser = $chat->getOtherUser();
        $this->selectedChat = $chat;

        $this->loadMessages();
        $this->dispatch('chatSelected');
    }



    public function resetLoadMoreTrigger(int $height)
    {
        $this->height = $height;
    }
    public function loadMoreMessages()
    {
        $messagesCount = $this->selectedChat->messages()->count();
        if ($this->limit >= $messagesCount) {
            return;
        }
        $this->limit += 10;
        $this->loadMessages();

        $this->dispatch('messagesLoaded', height: $this->height);
    }

    public function sendMessage()
    {
        $content = trim($this->message);

        if (!$this->selectedChat || !$this->selectedUser || $content === '') {
            return;
        }

        $message = \App\Models\Message::create([
            'chat_id' => $this->selectedChat->id,
            'sender_id' => auth()->id(),
            'content' => $content,
        ]);

        $this->selectedChat->update(['last_message_at' => now()]);
        $this->messages->push($message);
        $this->message = '';
        $this->loadChats();

        $this->dispatch('messageSent');

        broadcast(new MessageSent($message->id, $this->selectedUser->id));
    }

    public function loadMessages()
    {
        if (!$this->selectedChat) {
            return;
        }

        $this->messages = $this->selectedChat->loadMessages($this->limit);
        $unreadMessageCount = $this->selectedChat->unreadMessages()->count();


        if ($unreadMessageCount > 0) {
            $this->selectedChat->markMessagesAsRead();
            broadcast(new \App\Events\MessageRead($this->selectedChat->id, $this->selectedUser->id));
        }
    }

    public function toggleSearch()
    {
        $this->viewmode = $this->viewmode === 'chats' ? 'search' : 'chats';
        // تفريغ البحث عند التبديل إذا أردت
        if ($this->viewmode === 'chats') {
            $this->search = '';
        }
    }

    public function selectUser($userId)
    {
        $user = User::find($userId);
        if (!$user) return;

        $chats = \App\Models\Chat::findOrCreate(auth()->id(), $userId);
        $this->viewmode = 'chats';

        $this->selectChat($chats->id);
        $this->loadChats();
    }

    public function loadChats()
    {

        $this->chats = auth()->user()->chats()->with([
            'userOne',
            'userTwo',
            'messages'
        ])->orderBy('last_message_at', 'desc')->get();
    }
    public function getListeners(): array
    {
        return [
            'echo-private:chat.' . auth()->id() . ',.MessageSent' => 'messageReceived',
            'echo-private:chat.' . auth()->id() . ',.MessageRead' => 'messagesRead',
            'loadMoreMessages',
            'resetLoadMoreTrigger',
        ];
    }
    public function messagesRead($data)
    {
        if (!$this->selectedChat) return;

        if ($data['chatId'] === $this->selectedChat->id) {
            $this->loadMessages();
        }
    }
    public function messageReceived($data)
    {
        $message = Message::find($data['messageId']);
        if (!$message) return;

        $this->loadChats();
        if ($this->selectedChat && $message->chat_id === $this->selectedChat->id) {
            $this->loadMessages();

            $this->dispatch('newMessageReceived');
        }
    }


    public function mount()
    {

        $this->loadChats();
    }

    public function render()
    {
        $users = [];

        if ($this->viewmode === 'search' && !empty($this->search)) {
            $users = Profile::where('username', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->get();
        }

        return view('livewire.chat', [
            'users' => $users
        ]);
    }
}
