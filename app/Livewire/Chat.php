<?php

namespace App\Livewire;

use Livewire\Component;

namespace App\Livewire;

use App\Models\Profile;
use Livewire\Component;
use App\Models\User; 

class Chat extends Component
{
    public $viewmode = 'chats';
    public $search = '';
    public $chats = [];
   
    public $selectedChat = null;


    public function selectChat($chatId)
    {
        
        $chat = \App\Models\Chat::find($chatId);
        if (!$chat || !$chat->isChatContainerUser(auth()->id())) {
            return;
         }
         $this->selectedChat = $chat;
        
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
        if (!User::find($userId)) return;

        $chats = \App\Models\Chat::findOrCreate(auth()->id(), $userId);
        $this->viewmode = 'chats';
         $this->selectedChat = $chats;
        $this->loadChats();
    }

    public function loadChats()
    {
       
        $this->chats = auth()->user()->chats()->with([
            'userOne',
            'userTwo',
            'messages'
        ])->get();
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
