<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Story;
use App\Models\StoryView;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class Chat extends Component
{
    use WithFileUploads;

    // ==================== Chat ====================
    public $viewmode = 'chats';
    public $search = '';
    public $chats = [];
    public $messages = null;
    public $selectedChat = null;
    public $message = '';
    public $selectedUser = null;
    public $limit = 10;
    public $height;

    // ==================== Story State ====================
    public $storyUsers = [];       // Other users' stories for the bar
    public $myStories;             // Auth user's own stories collection
    public $myStorySeen = true;    // Whether own stories are all seen

    // Modals
    public $showMyStoryOptions = false;
    public $showAddStory = false;

    // Add Story Form
    public $storyImage;
    public $storyText = '';

    // Viewer
    public $viewerOpen = false;
    public $viewerStories;         // Collection of stories being viewed
    public $viewerIndex = 0;
    public $viewerUser = null;     // The user whose stories are shown
    public $viewerIsOwn = false;
    public $usersseen = [];

    public $showViewsSheet = false;
    public $storyViewers = [];
    public $selectedStoryId;
    // ==================== Mount ====================
    public function mount()
    {
        $this->myStories   = collect();
        $this->viewerStories = collect();
        $this->loadChats();
        $this->loadStories();
    }


    // ==================== View Views ====================


    public function openViewsSheet($storyId)
    {
        $this->selectedStoryId = $storyId;

        $this->storyViewers = StoryView::with('profile')
            ->where('story_id', $storyId)
            ->latest()
            ->get();

        $this->showViewsSheet = true;
    }

    public function closeViewsSheet()
    {
        $this->showViewsSheet = false;
    }




    // ==================== Story: Load ====================
    public function loadStories()
    {
        $userId = auth()->id();

        // My stories
        $this->myStories = Story::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->with('views')
            ->orderBy('created_at', 'asc')
            ->get();





        // Check if user has seen all own stories (always true for owner, but track for ring style)
        $this->myStorySeen = $this->myStories->isEmpty();

        // Chat partner IDs
        $chatUserIds = \App\Models\Chat::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->get()
            ->map(fn($chat) => $chat->user_one_id == $userId ? $chat->user_two_id : $chat->user_one_id)
            ->unique()
            ->values();

        // Stories from chat partners
        $stories = Story::with(['user.profile', 'views'])
            ->whereIn('user_id', $chatUserIds)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'asc')
            ->get();

        // Group by user, mark seen, build storyUsers bar data
        $this->storyUsers = $stories
            ->groupBy('user_id')
            ->map(function ($userStories) use ($userId) {
                $allSeen = $userStories->every(fn($s) => $s->views->contains('user_id', $userId));
                $profile = $userStories->first()->user->profile;
                return (object) [
                    'user_id'  => $userStories->first()->user_id,
                    'username' => $profile->username,
                    'avatar'   => $profile->avatar,
                    'all_seen' => $allSeen,
                ];
            })
            ->values();
    }

    // ==================== Story: My Story Click ====================
    public function handleMyStoryClick()
    {
        if ($this->myStories->count() > 0) {
            // Has stories → show options
            $this->showMyStoryOptions = true;
        } else {
            // No stories → open add directly
            $this->openAddStory();
        }
    }

    // ==================== Story: Open Add ====================
    public function openAddStory()
    {
        $this->showMyStoryOptions = false;
        $this->showAddStory       = true;
        $this->storyImage         = null;
        $this->storyText          = '';
    }

    public function closeAddStory()
    {
        $this->showAddStory = false;
        $this->storyImage   = null;
        $this->storyText    = '';
    }

    // ==================== Story: View My Own ====================
    public function viewMyOwnStories()
    {
        $this->showMyStoryOptions = false;

        if ($this->myStories->isEmpty()) return;

        $this->viewerStories = $this->myStories;
        $this->viewerIndex   = 0;
        $this->viewerUser    = auth()->user();
        $this->viewerIsOwn   = true;
        $this->viewerOpen    = true;

        $this->dispatch('storyViewerOpened', [
            'index' => 0,
            'total' => $this->viewerStories->count(),
        ]);
    }

    // ==================== Story: Open User Story ====================
    public function openUserStory($userId)
    {
        $authId   = auth()->id();
        $user     = User::with('profile')->find($userId);
        if (!$user) return;

        $stories = Story::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->withCount('views')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($stories->isEmpty()) return;

        // Find first unseen
        $firstUnseen = $stories->first(fn($s) => !$s->views->contains('user_id', $authId));
        $startIndex  = $firstUnseen
            ? $stories->search(fn($s) => $s->id === $firstUnseen->id)
            : 0;

        $this->viewerStories = $stories;
        $this->viewerIndex   = $startIndex;
        $this->viewerUser    = $user;
        $this->viewerIsOwn   = false;
        $this->viewerOpen    = true;

        // Mark current story as viewed
        $this->markCurrentStoryViewed();

        $this->dispatch('storyViewerOpened', [
            'index' => $startIndex,
            'total' => $stories->count(),
        ]);
    }

    // ==================== Story: Viewer Navigation ====================
    public function nextViewerStory()
    {
        if (!$this->viewerOpen) return;

        if ($this->viewerIndex < $this->viewerStories->count() - 1) {
            $this->viewerIndex++;
            if (!$this->viewerIsOwn) $this->markCurrentStoryViewed();

            $this->dispatch('storyIndexChanged', [
                'index' => $this->viewerIndex,
                'total' => $this->viewerStories->count(),
            ]);
        } else {
            $this->closeStoryViewer();
        }
    }

    public function prevViewerStory()
    {
        if (!$this->viewerOpen || $this->viewerIndex <= 0) return;

        $this->viewerIndex--;

        $this->dispatch('storyIndexChanged', [
            'index' => $this->viewerIndex,
            'total' => $this->viewerStories->count(),
        ]);
    }

    public function closeStoryViewer()
    {
        $this->viewerOpen    = false;
        $this->viewerStories = collect();
        $this->viewerIndex   = 0;
        $this->viewerUser    = null;
        $this->viewerIsOwn   = false;
        $this->dispatch('storyViewerClosed');
    }

    // ==================== Story: Mark Viewed ====================
    private function markCurrentStoryViewed()
    {
        $story = $this->viewerStories->get($this->viewerIndex);
        if (!$story) return;

        StoryView::firstOrCreate([
            'story_id' => $story->id,
            'user_id'  => auth()->id(),
        ]);
    }

    // ==================== Story: Delete ====================
    public function deleteCurrentStory()
    {
        if (!$this->viewerIsOwn) return;

        $story = $this->viewerStories->get($this->viewerIndex);
        if (!$story) return;

        // Delete file from storage
        if ($story->media) {
            Storage::disk('public')->delete($story->media);
        }

        $story->delete();

        // Reload own stories
        $this->myStories = Story::where('user_id', auth()->id())
            ->where('expires_at', '>', now())
            ->withCount('views')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($this->myStories->isEmpty()) {
            $this->closeStoryViewer();
            $this->loadStories();
            return;
        }

        // Adjust index
        $this->viewerStories = $this->myStories;
        $this->viewerIndex   = min($this->viewerIndex, $this->viewerStories->count() - 1);

        $this->loadStories();

        $this->dispatch('storyIndexChanged', [
            'index' => $this->viewerIndex,
            'total' => $this->viewerStories->count(),
        ]);
    }

    // ==================== Story: Upload ====================
    public function uploadStory()
    {
        $validated = Validator::make(
            ['storyImage' => $this->storyImage, 'storyText' => $this->storyText],
            [
                'storyImage' => 'required|image|max:5120',
                'storyText'  => 'nullable|string|max:160',
            ]
        );

        if ($validated->fails()) {
            foreach ($validated->errors()->all() as $error) {
                $this->addError('storyImage', $error);
            }
            return;
        }

        $path = $this->storyImage->store('stories', 'public');

        Story::create([
            'user_id'    => auth()->id(),
            'media'      => $path,
            'text'       => $this->storyText ?: null,
            'expires_at' => Carbon::now()->addHours(24),
        ]);

        $this->closeAddStory();
        $this->loadStories();
    }

    // ==================== Story: Validate Image on Change ====================
    public function updatedStoryImage()
    {


        Validator::make([
            'storyImage' => $this->storyImage,
        ], [
            'storyImage' => 'required|image|max:5120',
        ])->validate();
    }

    // ==================== Chat: Select ====================
    public function selectChat($chatId)
    {
        $this->limit = 10;
        $chat = \App\Models\Chat::find($chatId);
        if (!$chat || !$chat->isChatContainerUser(auth()->id())) return;

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
        $count = $this->selectedChat->messages()->count();
        if ($this->limit >= $count) return;
        $this->limit += 10;
        $this->loadMessages();
        $this->dispatch('messagesLoaded', height: $this->height);
    }

    public function sendMessage()
    {
        $content = trim($this->message);
        if (!$this->selectedChat || !$this->selectedUser || $content === '') return;

        $message = Message::create([
            'chat_id'   => $this->selectedChat->id,
            'sender_id' => auth()->id(),
            'content'   => $content,
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
        if (!$this->selectedChat) return;

        $this->messages = $this->selectedChat->loadMessages($this->limit);
        $unread = $this->selectedChat->unreadMessages()->count();

        if ($unread > 0) {
            $this->selectedChat->markMessagesAsRead();
            broadcast(new \App\Events\MessageRead($this->selectedChat->id, $this->selectedUser->id));
        }
    }

    public function toggleSearch()
    {
        $this->viewmode = $this->viewmode === 'chats' ? 'search' : 'chats';
        if ($this->viewmode === 'chats') $this->search = '';
    }

    public function selectUser($userId)
    {
        $user = User::find($userId);
        if (!$user) return;

        $chat = \App\Models\Chat::findOrCreate(auth()->id(), $userId);
        $this->viewmode = 'chats';
        $this->selectChat($chat->id);
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

    // ==================== Listeners ====================
    public function getListeners(): array
    {
        return [
            'echo-private:chat.' . auth()->id() . ',.MessageSent' => 'messageReceived',
            'echo-private:chat.' . auth()->id() . ',.MessageRead' => 'messagesRead',
            'nextViewerStory',
            'closeStoryViewer',
            'loadMoreMessages',
            'resetLoadMoreTrigger',
        ];
    }

    public function messagesRead($data)
    {
        if (!$this->selectedChat) return;
        if ($data['chatId'] === $this->selectedChat->id) $this->loadMessages();
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

    // ==================== Render ====================
    public function render()
    {
        $users = [];
        if ($this->viewmode === 'search' && !empty($this->search)) {
            $users = Profile::where('username', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->get();
        }

        return view('livewire.chat', ['users' => $users]);
    }
}
