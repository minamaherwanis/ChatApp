<div class="chat-container">

    <!-- Sidebar -->
    <aside class="chat-sidebar">
        <div class="chat-header">
            <h1> {{ $viewmode === 'chats' ? 'Chats' : 'Users' }}</h1>
            <div class="chat-header-actions">
                <button class="icon-button" wire:click="toggleSearch" title="Start new chat">
                    {{ $viewmode === 'chats' ? '➕' : '💬' }}
                </button>
                <button class="icon-button" title="More options">⋯</button>

            </div>
        </div>

        {{-- ==================== STORY BAR ==================== --}}
        <div class="stories-bar">

            {{-- My Story --}}
            <div class="story-item" wire:click="handleMyStoryClick">
                <div
                    class="story-avatar-wrapper {{ $myStories->count() ? ($myStorySeen ? 'seen' : 'unseen') : 'no-story' }}">
                    @if (auth()->user()->profile->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" class="story-avatar">
                    @else
                        <div class="story-avatar-placeholder">
                            {{ strtoupper(substr(auth()->user()->profile->username, 0, 2)) }}
                        </div>
                    @endif
                    @if (!$myStories->count())
                        <div class="story-add-badge">+</div>
                    @endif
                </div>
                <span class="story-label">My Status</span>
            </div>

            {{-- Other Users Stories --}}
            @foreach ($storyUsers as $storyUser)
                <div class="story-item" wire:click="openUserStory({{ $storyUser->user_id }})">
                    <div class="story-avatar-wrapper {{ $storyUser->all_seen ? 'seen' : 'unseen' }}">
                        @if ($storyUser->avatar)
                            <img src="{{ asset('storage/' . $storyUser->avatar) }}" class="story-avatar">
                        @else
                            <div class="story-avatar-placeholder">
                                {{ strtoupper(substr($storyUser->username, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                    <span class="story-label">{{ $storyUser->username }}</span>
                </div>
            @endforeach

        </div>

        {{-- ==================== MY STORY OPTIONS MODAL ==================== --}}
        @if ($showMyStoryOptions)
            <div class="story-modal-overlay" wire:click.self="$set('showMyStoryOptions', false)">
                <div class="story-options-sheet">
                    <div class="story-options-handle"></div>
                    <button class="story-option-btn" wire:click="openAddStory">
                        <span class="story-option-icon">📷</span>
                        <div>
                            <div class="story-option-title">Add New Story</div>
                            <div class="story-option-sub">Share a new photo or moment</div>
                        </div>
                    </button>
                    <button class="story-option-btn" wire:click="viewMyOwnStories">
                        <span class="story-option-icon">👁</span>
                        <div>
                            <div class="story-option-title">View My Stories</div>
                            <div class="story-option-sub">{{ $myStories->count() }} active
                                {{ Str::plural('story', $myStories->count()) }}</div>
                        </div>
                    </button>
                    <button class="story-option-btn cancel" wire:click="$set('showMyStoryOptions', false)">
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        {{-- ==================== ADD STORY MODAL ==================== --}}
        @if ($showAddStory)
            <div class="story-modal-overlay" wire:click.self="closeAddStory">
                <div class="add-story-modal">
                    <div class="add-story-header">
                        <h3>New Story</h3>
                        <button wire:click="closeAddStory" class="close-modal-btn">✕</button>
                    </div>

                    {{-- Image Preview or Upload Area --}}
                    @if ($storyImage)
                        <div class="story-image-preview-wrap">
                            <img src="{{ $storyImage->temporaryUrl() }}" class="story-preview-img">
                            <button class="change-image-btn" wire:click="$set('storyImage', null)">Change Photo</button>
                        </div>
                    @else
                        <label for="storyFileInput" class="story-upload-area">
                            <div class="upload-icon">📷</div>
                            <div class="upload-text">Click to choose a photo</div>
                            <div class="upload-sub">JPG, PNG, GIF up to 5MB</div>
                        </label>
                        <input type="file" id="storyFileInput" accept="image/*" style="display:none;"
                            wire:model="storyImage">
                    @endif

                    {{-- Description --}}
                    <div class="story-desc-wrap">
                        <textarea wire:model="storyText" class="story-desc-input" placeholder="Add a description... (optional)" maxlength="160"
                            rows="3"></textarea>
                        <div class="story-char-count {{ strlen($storyText) > 140 ? 'near-limit' : '' }}">
                            {{ strlen($storyText) }}/160
                        </div>
                    </div>

                    @error('storyImage')
                        <div class="story-error">{{ $message }}</div>
                    @enderror
                    @error('storyText')
                        <div class="story-error">{{ $message }}</div>
                    @enderror

                    <div class="add-story-actions">
                        <button class="story-cancel-btn" wire:click="closeAddStory">Cancel</button>
                        <button class="story-upload-btn {{ !$storyImage ? 'disabled' : '' }}" wire:click="uploadStory"
                            {{ !$storyImage ? 'disabled' : '' }}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            Share Story
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== STORY VIEWER OVERLAY ==================== --}}
        @if ($viewerOpen && $viewerStories->count())
            <div class="viewer-overlay" id="storyViewerOverlay">

                {{-- Progress Bars --}}
                <div class="viewer-progress-row">
                    @foreach ($viewerStories as $i => $story)
                        <div class="viewer-progress-bar">
                            <div class="viewer-progress-fill" id="prog-{{ $i }}"
                                style="width: {{ $i < $viewerIndex ? '100%' : '0%' }}"></div>
                        </div>
                    @endforeach
                </div>

                {{-- Header --}}
                <div class="viewer-header">
                    <div class="viewer-user-info">
                        @if ($viewerUser && $viewerUser->profile->avatar)
                            <img src="{{ asset('storage/' . $viewerUser->profile->avatar) }}" class="viewer-avatar">
                        @else
                            <div class="viewer-avatar-placeholder">
                                {{ strtoupper(substr($viewerUser?->profile?->username ?? 'U', 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <div class="viewer-username">{{ $viewerUser?->profile?->username }}</div>
                            <div class="viewer-time">
                                {{ $viewerStories[$viewerIndex]->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    <div class="viewer-header-actions">
                        @if ($viewerIsOwn)
                            <button class="viewer-delete-btn" wire:click="deleteCurrentStory" title="Delete story">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path
                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                </svg>
                            </button>
                        @endif
                        <button class="viewer-close-btn" wire:click="closeStoryViewer">✕</button>
                    </div>
                </div>

                {{-- Story Image --}}
                <div class="viewer-body">
                    {{-- Tap Zones --}}
                    <div class="tap-left" wire:click="prevViewerStory"></div>
                    <div class="tap-right" wire:click="nextViewerStory"></div>

                    <img src="{{ asset('storage/' . $viewerStories[$viewerIndex]->media) }}" class="viewer-image"
                        id="viewerImage">

                    @if ($viewerStories[$viewerIndex]->text)
                        <div class="viewer-caption">{{ $viewerStories[$viewerIndex]->text }}</div>
                    @endif
                </div>

                {{-- Footer - Owner Only: Views --}}
                @if ($viewerIsOwn)
                    <div class="viewer-footer-owner">
                        <div class="viewer-views-info">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <span class="views-click"
                                wire:click="openViewsSheet({{ $viewerStories[$viewerIndex]->id }})">{{ $viewerStories[$viewerIndex]->views->count() ?? 0 }}
                                views</span>
                        </div>
                    </div>
                @endif
                @if ($showViewsSheet)
                    <div class="bottom-sheet-overlay" wire:click="closeViewsSheet"></div>

                    <div class="bottom-sheet">
                        <div class="sheet-header">
                            <h3>Views</h3>
                            <button wire:click="closeViewsSheet">✕</button>
                        </div>

                        <div class="sheet-body">
                            @forelse($storyViewers as $view)
                                <div class="viewer-item">
                                    <img
                                        src="{{ $view->profile->avatar ? asset('storage/' . $view->profile->avatar) : asset('default.png') }}" />
                                    <div>
                                        <div class="name">{{ $view->profile->name }}</div>
                                        <div class="time">{{ $view->viewed_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @empty
                                <p>No views yet</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="search-box">
            <input type="text" placeholder="Search chats..." id="searchInput" wire:model.live="search">
        </div>

        @if ($viewmode === 'chats')
            <div class="chat-list">
                @forelse ($chats as $chat)
                    <?php $otheruser = $chat->getOtherUser(); ?>

                    <a wire:click="selectChat({{ $chat->id }})" href="#" class="chat-item"
                        wire:key="{{ $chat->id }}">
                        <div class="chat-avatar" style="background: #37b24d;">


                            @if ($otheruser->profile->avatar)
                                <img src="{{ asset('storage/' . $otheruser->profile->avatar) }}"
                                    alt="{{ $otheruser->profile->username }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                                {{ strtoupper(substr($otheruser->profile->username, 0, 2)) }}
                            @endif
                        </div>
                        <div class="chat-preview">
                            <div class="chat-name">{{ $otheruser->profile->username }}</div>
                            <div class="chat-message">
                                {{ $chat->lastMessage() ? $chat->lastMessage()->content : 'No messages yet.' }}</div>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                            <div class="chat-time">{{ $chat->last_message_formatted }}</div>

                            @if ($chat->unreadMessages()->count() > 0)
                                <span class="unreadstyle">{{ $chat->unreadMessages()->count() }}</span>
                            @endif
                        </div>


                    </a>
                @empty
                    <div style="padding: 20px; text-align: center; color: #888;">
                        {{ $viewmode === 'chats' ? 'No chats found.' : 'Type to search...' }}
                    </div>
                @endforelse
            </div>
        @else
            @forelse ($users as $user)
                <a href="#" class="chat-item" wire:key="{{ $user->user_id }}"
                    wire:click="selectUser({{ $user->user_id }})">
                    <div class="chat-avatar" style="background: #37b24d;">
                        @if ($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->username }}"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            {{ strtoupper(substr($user->username, 0, 2)) }}
                        @endif
                    </div>
                    <div class="chat-preview">
                        <div class="chat-name">{{ '@' . $user->username }}</div>
                        <div class="chat-message">{{ $user->bio }}</div>
                    </div>
                    <div class="chat-time">Online</div>
                </a>
            @empty
                <div style="padding: 20px; text-align: center; color: #888;">
                    {{ $search ? 'No users found.' : 'Type to search...' }}
                </div>
            @endforelse
        @endif

    </aside>

    <!-- Main content -->
    <div class="chat-content" style="flex-direction: column; justify-content: flex-start; padding: 0;">
        <!-- Stories Section -->


        <!-- Empty State -->
        @if ($selectedChat)

            @if ($selectedChat)
                <div class="message-section">

                    {{-- Header --}}
                    <div class="msg-header">
                        <div class="msg-header-left">
                            <div class="chat-avatar" style="background: #37b24d;">


                                @if ($selectedUser->profile->avatar)
                                    <img src="{{ asset('storage/' . $selectedUser->profile->avatar) }}"
                                        alt="{{ $selectedUser->profile->username }}"
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                @else
                                    {{ strtoupper(substr($selectedUser->profile->username, 0, 2)) }}
                                @endif
                            </div>
                            <div class="msg-peer-info">
                                <span class="msg-peer-name">{{ $selectedUser->profile->name }}</span>
                                <span class="msg-peer-status"><span class="status-dot"></span> Online</span>
                            </div>
                        </div>
                        <div class="msg-header-actions">
                            <button class="icon-button" title="Voice call">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                                </svg>
                            </button>
                            <button class="icon-button" title="Video call">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polygon points="23 7 16 12 23 17 23 7" />
                                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                                </svg>
                            </button>
                            <button class="icon-button">⋯</button>
                        </div>
                    </div>

                    {{-- Messages --}}
                    <div class="msg-body" id="msgBody">

                        <div id="load-more-indicator" class="hidden flex justify-center animate-fade-in">
                            <div
                                class="bg-zinc-700 text-zinc-300 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm">
                                Load more...
                            </div>
                        </div>
                        @if ($messages)
                            @foreach ($messages as $message)
                                @if (!$message->isSender())
                                    <div class="msg-row msg-theirs">
                                        <div class="chat-avatar msg-avatar"
                                            style="width:32px;height:32px;font-size:0.7rem;background:#1e5ba5;flex-shrink:0;">
                                            @if ($selectedUser->profile->avatar)
                                                <img src="{{ asset('storage/' . $selectedUser->profile->avatar) }}"
                                                    alt="{{ $selectedUser->profile->username }}"
                                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                            @else
                                                {{ strtoupper(substr($selectedUser->profile->username, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div class="msg-bubble-wrap">
                                            <div class="msg-bubble bubble-theirs">{{ $message->content }}</div>
                                            <span class="msg-time">{{ $message->created_at }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="msg-row msg-mine">
                                        <div class="msg-bubble-wrap">
                                            <div class="msg-bubble bubble-mine">{{ $message->content }}</div>
                                            <div style="display: flex; align-items: center; gap: 3px;">
                                                <span class="msg-time">{{ $message->created_at }}</span>
                                                @if ($message->is_read)
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 text-cyan-400"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z">
                                                        </path>
                                                    </svg>
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 text-cyan-400 -ml-2"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z">
                                                        </path>
                                                    </svg>
                                                @else
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 text-zinc-500"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z">
                                                        </path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                        @endif
                        {{-- <div class="msg-row msg-theirs">
                            <div class="chat-avatar msg-avatar"
                                style="width:32px;height:32px;font-size:0.7rem;background:#1e5ba5;flex-shrink:0;">AH
                            </div>
                            <div class="msg-bubble-wrap">
                                <div class="msg-bubble bubble-theirs">Hey! How are you doing?</div>
                                <span class="msg-time">10:30</span>
                            </div>
                        </div>

                        <div class="msg-row msg-mine">
                            <div class="msg-bubble-wrap">
                                <div class="msg-bubble bubble-mine">I'm good thanks! You?</div>
                                <span class="msg-time">10:31</span>
                            </div>
                        </div> --}}

                    </div>


                    {{-- Input --}}
                    <form wire:submit.prevent="sendMessage" class="msg-footer">
                        {{-- <button class="icon-button attach-btn" title="Attach">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                            </svg>
                        </button> --}}
                        <div class="msg-input-wrap">
                            <input wire:model="message" type="text" class="msg-input"
                                placeholder="Type a message...">
                        </div>
                        <button class="send-btn" title="Send">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                        </button>
                </div>

                </form>
            @else
                <div
                    style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2rem;padding:2rem;">
                    <div class="empty-state">
                        <div class="empty-state-icon">💬</div>
                        <h2>Select a chat</h2>
                        <p>Choose a conversation from the sidebar to start messaging. Or create a new chat to connect
                            with friends.</p>
                    </div>
                </div>
            @endif
        @else
            <div
                style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2rem; padding: 2rem;">
                <div class="empty-state">
                    <div class="empty-state-icon">💬</div>
                    <h2>Select a chat</h2>
                    <p>Choose a conversation from the sidebar to start messaging. Or create a new chat to connect with
                        friends.</p>
                </div>
            </div>
        @endif
    </div>


</div>

@if ($storyImage)
    <div class="story-preview-overlay">
        <div class="story-preview-box">

            <img src="{{ $storyImage->temporaryUrl() }}" class="preview-img">

            <div class="preview-actions">
                <button class="cancel-btn" wire:click="$set('storyImage', null)">
                    Cancel
                </button>

                <button class="send-story" wire:click="uploadStory">
                    Upload
                </button>
            </div>

        </div>
    </div>
@endif
<style>
    .bottom-sheet-overlay {

        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(4px);
        z-index: 999;
    }

    .bottom-sheet {
        position: fixed;
        bottom: 50%;
        left: 50%;

        width: 50%;
        height: 60%;

        transform: translate(-50%, 50%);

        background: #111f33;
        border-radius: 16px;
        z-index: 1000;

        display: flex;
        flex-direction: column;

        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
        color: #f8fbff;

        animation: fadeScale 0.2s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Header */
    .sheet-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        background: #0f1b2b;
    }

    .sheet-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #f8fbff;
    }

    .sheet-header button {
        background: rgba(255, 255, 255, 0.08);
        border: none;
        color: #fff;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
    }

    /* Body */
    .sheet-body {

        overflow-y: auto;
        padding: 10px;
    }

    /* Viewer item */
    .viewer-item {
        display: flex;
        gap: 12px;
        padding: 12px;
        align-items: center;
        background: #15263d;
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        margin-bottom: 8px;
        transition: 0.2s;
    }

    .viewer-item:hover {
        background: #1a2f4a;
        transform: translateY(-1px);
    }

    /* Avatar */
    .viewer-item img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(79, 162, 255, 0.3);
    }

    /* Text */
    .viewer-item .name {
        font-size: 14px;
        font-weight: 600;
        color: #f8fbff;
    }

    .viewer-item .time {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.5);
    }

    /* ---- Story Bar ---- */
    .stories-bar {
        display: flex;
        gap: 12px;
        padding: 12px 14px 14px;
        overflow-x: auto;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        scrollbar-width: none;
    }

    .stories-bar::-webkit-scrollbar {
        display: none;
    }

    .story-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        min-width: 62px;
        transition: transform 0.18s;
    }

    .story-item:hover {
        transform: translateY(-2px);
    }

    .story-label {
        font-size: 11px;
        color: #a8b8cc;
        max-width: 64px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: center;
    }

    .story-avatar-wrapper {
        position: relative;
        padding: 2.5px;
        border-radius: 50%;
        background: #2a3b55;
    }

    .story-avatar-wrapper.unseen {
        background: linear-gradient(135deg, #4fa2ff 0%, #7953ff 100%);
    }

    .story-avatar-wrapper.seen {
        background: #2a3b55;
    }

    .story-avatar-wrapper.no-story {
        background: transparent;
        border: 2px dashed rgba(79, 162, 255, 0.6);
    }

    .story-avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #0f1b2b;
        display: block;
    }

    .story-avatar-placeholder {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: #1e3a5f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        color: #4fa2ff;
        border: 2px solid #0f1b2b;
    }

    .story-add-badge {
        position: absolute;
        bottom: 1px;
        right: 1px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #4fa2ff;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #0f1b2b;
        line-height: 1;
    }

    /* ---- Shared Modal Overlay ---- */
    .story-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9000;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        backdrop-filter: blur(4px);
        animation: fadeIn 0.2s ease;
    }

    /* ---- My Story Options Sheet ---- */
    .story-options-sheet {
        width: 100%;
        max-width: 480px;
        background: #111f33;
        border-radius: 20px 20px 0 0;
        padding: 12px 16px 32px;
        animation: slideUp 0.25s ease;
    }

    .story-options-handle {
        width: 36px;
        height: 4px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 2px;
        margin: 0 auto 20px;
    }

    .story-option-btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        background: #15263d;
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 14px;
        color: #f0f4fa;
        cursor: pointer;
        margin-bottom: 10px;
        text-align: left;
        transition: background 0.18s;
    }

    .story-option-btn:hover {
        background: #1c2f49;
    }

    .story-option-btn.cancel {
        background: transparent;
        border: none;
        color: #ff6b6b;
        justify-content: center;
        font-size: 15px;
        font-weight: 600;
        margin-top: 4px;
    }

    .story-option-icon {
        font-size: 22px;
    }

    .story-option-title {
        font-weight: 600;
        font-size: 14px;
    }

    .story-option-sub {
        font-size: 12px;
        color: #6d8aaa;
        margin-top: 2px;
    }

    /* ---- Add Story Modal ---- */
    .add-story-modal {
        width: 100%;
        max-width: 420px;
        background: #111f33;
        border-radius: 20px 20px 0 0;
        padding: 20px 20px 36px;
        animation: slideUp 0.25s ease;
    }

    .add-story-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .add-story-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #f0f4fa;
        margin: 0;
    }

    .close-modal-btn {
        background: rgba(255, 255, 255, 0.08);
        border: none;
        color: #a0b0c4;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-modal-btn:hover {
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
    }

    .story-upload-area {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 2px dashed rgba(79, 162, 255, 0.4);
        border-radius: 16px;
        padding: 36px 20px;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
        margin-bottom: 16px;
        background: rgba(79, 162, 255, 0.03);
    }

    .story-upload-area:hover {
        border-color: rgba(79, 162, 255, 0.7);
        background: rgba(79, 162, 255, 0.07);
    }

    .upload-icon {
        font-size: 36px;
    }

    .upload-text {
        font-size: 14px;
        font-weight: 600;
        color: #c0d0e0;
    }

    .upload-sub {
        font-size: 12px;
        color: #5a7a99;
    }

    .story-image-preview-wrap {
        position: relative;
        margin-bottom: 16px;
        border-radius: 16px;
        overflow: hidden;
    }

    .story-preview-img {
        width: 100%;
        max-height: 260px;
        object-fit: cover;
        display: block;
        border-radius: 16px;
    }

    .change-image-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.65);
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        backdrop-filter: blur(4px);
    }

    .story-desc-wrap {
        position: relative;
        margin-bottom: 16px;
    }

    .story-desc-input {
        width: 100%;
        background: #15263d;
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 12px;
        color: #f0f4fa;
        font-size: 14px;
        padding: 12px 14px 28px;
        resize: none;
        outline: none;
        box-sizing: border-box;
        font-family: inherit;
        transition: border-color 0.2s;
    }

    .story-desc-input:focus {
        border-color: rgba(79, 162, 255, 0.4);
    }

    .story-desc-input::placeholder {
        color: rgba(255, 255, 255, 0.25);
    }

    .story-char-count {
        position: absolute;
        bottom: 10px;
        right: 12px;
        font-size: 11px;
        color: rgba(255, 255, 255, 0.3);
    }

    .story-char-count.near-limit {
        color: #ffa94d;
    }

    .story-error {
        background: rgba(255, 80, 80, 0.12);
        border: 1px solid rgba(255, 80, 80, 0.3);
        color: #ff8080;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        margin-bottom: 12px;
    }

    .add-story-actions {
        display: flex;
        gap: 10px;
    }

    .story-cancel-btn {
        flex: 1;
        padding: 12px;
        background: rgba(255, 255, 255, 0.06);
        border: none;
        border-radius: 12px;
        color: #8a9bb0;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.18s;
    }

    .story-cancel-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .story-upload-btn {
        flex: 2;
        padding: 12px;
        background: #4fa2ff;
        border: none;
        border-radius: 12px;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.18s, transform 0.12s;
    }

    .story-upload-btn:hover {
        background: #3a8de8;
    }

    .story-upload-btn:active {
        transform: scale(0.97);
    }

    .story-upload-btn.disabled {
        background: #2a4060;
        color: #5a7a99;
        cursor: not-allowed;
    }

    /* ---- Story Viewer ---- */
    .viewer-overlay {
        position: fixed;
        inset: 0;
        background: #000;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        animation: fadeIn 0.2s ease;
    }

    .viewer-progress-row {
        display: flex;
        gap: 4px;
        padding: 12px 12px 0;
        z-index: 10;
        position: relative;
    }

    .viewer-progress-bar {
        flex: 1;
        height: 3px;
        background: rgba(255, 255, 255, 0.25);
        border-radius: 2px;
        overflow: hidden;
    }

    .viewer-progress-fill {
        height: 100%;
        background: #fff;
        border-radius: 2px;
        transition: width linear;
    }

    .viewer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px 8px;
        z-index: 10;
        position: relative;
    }

    .viewer-user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .viewer-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .viewer-avatar-placeholder {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #1e3a5f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #4fa2ff;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .viewer-username {
        font-size: 14px;
        font-weight: 600;
        color: #fff;
    }

    .viewer-time {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.55);
        margin-top: 1px;
    }

    .viewer-header-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .viewer-delete-btn {
        background: rgba(255, 80, 80, 0.2);
        border: none;
        color: #ff8080;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.18s;
    }

    .viewer-delete-btn:hover {
        background: rgba(255, 80, 80, 0.35);
    }

    .viewer-close-btn {
        background: rgba(255, 255, 255, 0.12);
        border: none;
        color: #fff;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .viewer-close-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .viewer-body {
        flex: 1;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .viewer-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .tap-left,
    .tap-right {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 38%;
        z-index: 5;
        cursor: pointer;
    }

    .tap-left {
        left: 0;
    }

    .tap-right {
        right: 0;
    }

    .viewer-caption {
        position: absolute;
        bottom: 1%;
        transform: translateY(50%);
        left: 0;
        right: 0;
        padding: 16px 24px;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        font-size: 15px;
        line-height: 1.5;
        z-index: 6;
        text-align: center;
        backdrop-filter: blur(4px);
        border-radius: 12px;
        margin: 0 20px;
        width: calc(100% - 40px);
    }

    .viewer-footer-owner {
        padding: 14px 20px 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.5);
    }

    .viewer-views-info {
        display: flex;
        align-items: center;
        gap: 8px;
        color: rgba(255, 255, 255, 0.75);
        font-size: 14px;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px 20px;
        border-radius: 20px;
        cursor: pointer;
        backdrop-filter: blur(4px);
    }

    /* ---- Animations ---- */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(60px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .tap-zone {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 40%;
        z-index: 20;
    }

    .tap-zone.left {
        left: 0;
    }

    .tap-zone.right {
        right: 0;
    }

    .own-story-footer {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        padding: 8px 20px;
        border-radius: 9999px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .view-count-number {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        z-index: 10000;
        display: flex;
        align-items: flex-end;
    }

    .modal-content {
        width: 100%;
        background: #15263d;
        border-radius: 16px 16px 0 0;
        padding: 10px;
    }

    .modal-option {
        width: 100%;
        padding: 16px;
        text-align: center;
        background: #1e2f4a;
        color: #fff;
        margin-bottom: 8px;
        border-radius: 12px;
        font-size: 1.1rem;
        border: none;
        cursor: pointer;
    }

    .modal-option.cancel {
        background: #2a2a2a;
        color: #ff5555;
    }

    .unreadstyle {
        background: #4fa2ff;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
    }

    .message-section {
        display: flex;
        flex-direction: column;
        height: 100vh;
        width: 100%;
        overflow: hidden;
    }

    .msg-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        background: #0f1b2b;
        flex-shrink: 0;
    }

    .msg-header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .msg-peer-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .msg-peer-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #f8fbff;
    }

    .msg-peer-status {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.5);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #40c057;
    }

    .msg-header-actions {
        display: flex;
        gap: 0.4rem;
    }

    .msg-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .msg-body::-webkit-scrollbar {
        width: 5px;
    }

    .msg-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .msg-body::-webkit-scrollbar-thumb {
        background: rgba(79, 162, 255, 0.2);
        border-radius: 3px;
    }

    .msg-row {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
        max-width: 70%;
    }

    .msg-mine {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .msg-theirs {
        align-self: flex-start;
    }

    .msg-avatar {
        margin-bottom: 18px;
    }

    .msg-bubble-wrap {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .msg-mine .msg-bubble-wrap {
        align-items: flex-end;
    }

    .msg-theirs .msg-bubble-wrap {
        align-items: flex-start;
    }

    .msg-bubble {
        padding: 0.65rem 1rem;
        border-radius: 18px;
        font-size: 0.9rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .bubble-mine {
        background: #4fa2ff;
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .bubble-theirs {
        background: #15263d;
        color: #f8fbff;
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-bottom-left-radius: 4px;
    }

    .msg-time {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.35);
        padding: 0 4px;
    }

    .msg-footer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        background: #0f1b2b;
        flex-shrink: 0;
    }

    .msg-input-wrap {
        flex: 1;
    }

    .msg-input {
        width: 100%;
        padding: 0.7rem 1.1rem;
        background: #15263d;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        color: #f8fbff;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .msg-input:focus {
        border-color: rgba(79, 162, 255, 0.4);
    }

    .msg-input::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .attach-btn {
        color: rgba(255, 255, 255, 0.45);
    }

    .attach-btn:hover {
        color: #4fa2ff;
        background: rgba(79, 162, 255, 0.12);
    }

    .send-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #4fa2ff;
        border: none;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background 0.2s, transform 0.15s;
    }

    .send-btn:hover {
        background: #3a8de8;
    }

    .send-btn:active {
        transform: scale(0.93);
    }

    .send-btn:disabled,
    .send-btn.btn-disabled {
        background: #2a4a6b;
        cursor: not-allowed;
        opacity: 0.5;
    }
</style>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        // =========================
        // STORY VIEWER (CLEAN VERSION)
        // =========================

        let progressTimer = null;
        const storyDuration = 10000; // 10s

        function clearProgress() {
            if (progressTimer) {
                clearTimeout(progressTimer);
                progressTimer = null;
            }
        }

        function startProgress(index) {
            clearProgress();

            const fill = document.getElementById('prog-' + index);
            if (!fill) return;

            // reset instantly
            fill.style.transition = 'none';
            fill.style.width = '0%';

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    fill.style.transition = `width ${storyDuration / 1000}s linear`;
                    fill.style.width = '100%';
                });
            });

            progressTimer = setTimeout(() => {
                Livewire.dispatch('nextViewerStory');
            }, storyDuration);
        }

        // =========================
        // LIVEWIRE EVENTS (STORY)
        // =========================

        Livewire.on('storyViewerOpened', (data) => {
            setTimeout(() => startProgress(data.index), 80);
        });

        Livewire.on('storyIndexChanged', (data) => {
            setTimeout(() => startProgress(data.index), 50);
        });

        Livewire.on('storyViewerClosed', () => {
            clearProgress();
        });

        // keyboard navigation
        document.addEventListener('keydown', function(e) {
            const viewer = document.getElementById('storyViewerOverlay');
            if (!viewer) return;

            if (e.key === 'ArrowRight') Livewire.dispatch('nextViewerStory');
            if (e.key === 'ArrowLeft') Livewire.dispatch('prevViewerStory');
            if (e.key === 'Escape') Livewire.dispatch('closeStoryViewer');
        });


        // =========================
        // CHAT UI HELPERS
        // =========================

        function scrollToBottom() {
            const box = document.querySelector(".msg-body");
            if (!box) return;

            requestAnimationFrame(() => {
                box.scrollTo({
                    top: box.scrollHeight,
                    behavior: "smooth"
                });
            });
        }

        function updateSendButton() {
            const input = document.querySelector(".msg-input");
            const btn = document.querySelector(".send-btn");
            if (!input || !btn) return;

            const hasText = input.value && input.value.trim().length > 0;

            btn.disabled = !hasText;
            btn.classList.toggle("btn-disabled", !hasText);
        }

        function initChatInput() {
            const input = document.querySelector(".msg-input");
            const form = document.querySelector(".msg-footer");

            if (!input || !form) return;

            input.addEventListener("input", updateSendButton);

            form.addEventListener("submit", () => {
                setTimeout(scrollToBottom, 30);
            });

            updateSendButton();
        }

        function handleChatSelected() {
            setTimeout(() => {
                initChatInput();
                scrollToBottom();
            }, 80);
        }

        // =========================
        // LIVEWIRE EVENTS (CHAT)
        // =========================

        window.addEventListener("chatSelected", handleChatSelected);
        window.addEventListener("messageSent", scrollToBottom);

        Livewire.on("messageSent", scrollToBottom);

    });
    document.addEventListener("DOMContentLoaded", function() {

        // Handle mobile view for chat selection
        window.addEventListener("chatSelected", handleChatSelection);

        // Listen for message sent event to scroll to bottom
        window.addEventListener("messageSent", handleMessageSent);

        // Listen for messages loaded more event to maintain scroll position
        window.addEventListener('messagesLoaded', handleLoadingMore);

        // Handle message input and send button
        function handleInput() {
            let messageForm = document.querySelector(".msg-footer");
            let messageInput = document.querySelector(".msg-input");

            if (messageForm && messageInput) {
                messageInput.focus();
                updateBtn();
                messageInput.addEventListener('input', updateBtn);
                messageForm.addEventListener('submit', e => {
                    e.preventDefault();
                    messageInput.value = '';
                    messageInput.focus();
                    updateBtn();
                    scrollToBottom();
                });
            }
        }

        // Update send button state
        function updateBtn() {
            let sendButton = document.querySelector(".send-btn");
            let messageInput = document.querySelector(".msg-input");
            if (!sendButton || !messageInput) return;

            const hasText = v => v && v.trim().length > 0;
            sendButton.disabled = !hasText(messageInput.value);
            sendButton.classList.toggle('btn-disabled', sendButton.disabled);
        }

        // Handle scrolling in messages area for loading more messages
        function handleScrolling() {
            let messagesArea = document.querySelector(".msg-body");
            let loadMoreIndicator = document.querySelector(".load-more-indicator");
            if (!messagesArea) return;

            messagesArea.addEventListener("scroll", () => {
                if (messagesArea.scrollTop === 0) {
                    if (loadMoreIndicator) loadMoreIndicator.classList.remove("hidden");
                    setTimeout(() => {
                        Livewire.dispatch('loadMoreMessages');
                    }, 20);
                }
            });
        }

        // Handle loading more messages to maintain scroll position
        function handleLoadingMore(event) {
            let messagesArea = document.querySelector(".msg-body");
            let loadMoreIndicator = document.querySelector(".load-more-indicator");
            if (messagesArea) {
                setTimeout(() => {
                    const previousHeight = event.detail.height;
                    const newHeight = messagesArea.scrollHeight;
                    requestAnimationFrame(() => {
                        messagesArea.scrollTo({
                            top: newHeight - previousHeight,
                            behavior: "auto",
                        });
                    });
                    if (loadMoreIndicator) loadMoreIndicator.classList.add("hidden");

                    Livewire.dispatch('resetLoadMoreTrigger', {
                        height: messagesArea.scrollHeight
                    });
                }, 20);
            }
        }

        // Function to scroll to bottom of messages
        function scrollToBottom() {
            let messagesArea = document.querySelector(".msg-body");
            if (messagesArea) {
                requestAnimationFrame(() => {
                    messagesArea.scrollTo({
                        top: messagesArea.scrollHeight,
                        behavior: "smooth",
                    });
                });
            }
        }

        // Handle back button
        function handleBackButton() {
            let backButton = document.querySelector(".back-button");
            if (!backButton) return;

            let chatArea = document.querySelector(".chat-content");
            let leftSidebar = document.querySelector(".chat-sidebar");
            backButton.addEventListener("click", () => {
                if (window.innerWidth < 768) {
                    leftSidebar.style.display = "flex";
                    chatArea.style.display = "none";
                }
            });
        }

        // Handle chat selection
        function handleChatSelection(event) {
            let chatArea = document.querySelector(".chat-content");
            let leftSidebar = document.querySelector(".chat-sidebar");
            if (window.innerWidth < 768) {
                leftSidebar.style.display = "none";
                chatArea.style.display = "flex";
            }

            setTimeout(() => {
                let messagesArea = document.querySelector(".msg-body");
                let messageInput = document.querySelector(".msg-input");
                if (messagesArea && messageInput) {
                    const height = messagesArea.scrollHeight;
                    messagesArea.scrollTop = height;

                    handleInput();
                    handleScrolling();
                    handleBackButton();

                    Livewire.dispatch('resetLoadMoreTrigger', {
                        height: messagesArea.scrollHeight
                    });
                }
            }, 50);
        }

        // Listen for message sent event to scroll to bottom
        function handleMessageSent() {
            setTimeout(() => {
                updateBtn();
                scrollToBottom();
            }, 20);
        }
    });
</script>
