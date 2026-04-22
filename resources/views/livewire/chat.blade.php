<div class="chat-container">

    <!-- Sidebar -->
    <aside class="chat-sidebar">
        <div class="chat-header">
            <h1> {{ $viewmode === 'chats' ? 'Chats' : 'Users' }}</h1>
            <div class="chat-header-actions">
                <button class="icon-button" wire:click="toggleSearch" title="Start new chat">
                    {{ $viewmode === 'chats' ? '➕' : '💬' }}
                </button>
                <a href="/profile" class="icon-button" title="Profile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M20 21a8 8 0 0 0-16 0"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
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
        @if ($viewmode != 'chats')
            <div class="search-box">
                <input type="text" placeholder="Search chats..." id="searchInput" wire:model.live="search">
            </div>
        @endif
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
                            <div class="chat-name">
                                {{ $otheruser->profile->name }}

                                @if (optional($otheruser->profile)->last_seen && $otheruser->profile->last_seen->gt(now()->subMinute()))
                                    <span class="online-badge">
                                        <span class="online-circle"></span>

                                    </span>
                                @endif
                            </div>
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
                            <a wire:click.prevent="backToChats()" class="chat-back-btn">
                                ←
                            </a>

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
                                <span class="msg-peer-name">
                                    {{ $selectedUser->profile->name }}
                                </span>

                                <span class="msg-peer-status">
                                    @if ($selectedUser->profile->last_seen && $selectedUser->profile->last_seen->gt(now()->subMinute()))

                                        <span class="status-dot online"></span>
                                        Online
                                    @else
                                        <span class="status-dot offline"></span>

                                        @if ($selectedUser->profile->last_seen)
                                            Last seen
                                            {{ $selectedUser->profile->last_seen->diffForHumans() }}
                                        @else
                                            Offline
                                        @endif

                                    @endif
                                </span>
                            </div>
                        </div>

                    </div>

                    {{-- Messages --}}
                    <div class="msg-body" id="msgBody">


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

                    </div>


                    {{-- Input --}}
                    <div class="msg-footer">
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
                        <button wire:click="sendMessage" class="send-btn" title="Send">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                        </button>
                </div>
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



<script>
    // ═══════════════════════════════════════════════════
    // MOBILE STATE — نحفظ هل المستخدم داخل شات أم لا
    // ═══════════════════════════════════════════════════
    let _mobileInChat = false;

    function isMobile() {
        return window.innerWidth < 768;
    }

    function applyMobileState() {
        const sidebar = document.querySelector('.chat-sidebar');
        const content = document.querySelector('.chat-content');
        if (!sidebar || !content) return;

        if (isMobile() && _mobileInChat) {
            sidebar.classList.add('hidden-mobile');
            content.classList.add('mobile-active');
        } else {
            sidebar.classList.remove('hidden-mobile');
            content.classList.remove('mobile-active');
        }
    }

    // ✅ الطريقة الصحيحة في Livewire v3 — بتشتغل بعد كل request (sendMessage, selectChat, إلخ)
    document.addEventListener('DOMContentLoaded', function() {
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => queueMicrotask(applyMobileState));
        });
    });

    document.addEventListener('DOMContentLoaded', function() {

        // ─── Mobile Navigation ───────────────────────────────
        window.addEventListener('chatSelected', function() {
            _mobileInChat = true;
            applyMobileState();
            setTimeout(scrollToBottom, 80);
            setTimeout(initChatInput, 100);
            setTimeout(handleScrolling, 100);
        });

        window.addEventListener('chatClosed', function() {
            _mobileInChat = false;
            applyMobileState();
        });

        window.addEventListener('resize', function() {
            if (!isMobile()) {
                _mobileInChat = false;
                applyMobileState();
            }
        });

        // ─── Chat Helpers ────────────────────────────────────
        function scrollToBottom() {
            const box = document.querySelector('.msg-body');
            if (!box) return;
            requestAnimationFrame(() => box.scrollTo({ top: box.scrollHeight, behavior: 'smooth' }));
        }

        function updateSendButton() {
            const input = document.querySelector('.msg-input');
            const btn   = document.querySelector('.send-btn');
            if (!input || !btn) return;
            const hasText = input.value.trim().length > 0;
            btn.disabled = !hasText;
            btn.classList.toggle('btn-disabled', !hasText);
        }

        function initChatInput() {
            const input = document.querySelector('.msg-input');
            const btn   = document.querySelector('.send-btn');
            if (!input) return;
            input.removeEventListener('input', updateSendButton);
            input.addEventListener('input', updateSendButton);
            updateSendButton();
            input.focus();
        }

        function onFormSubmit() {
            setTimeout(() => { updateSendButton(); scrollToBottom(); }, 30);
        }

        function handleScrolling() {
            const messagesArea = document.querySelector('.msg-body');
            if (!messagesArea || messagesArea._scrollBound) return;
            messagesArea._scrollBound = true;
            messagesArea.addEventListener('scroll', () => {
                if (messagesArea.scrollTop === 0) {
                    setTimeout(() => Livewire.dispatch('loadMoreMessages'), 20);
                }
            });
        }

        // ─── Livewire Events (Chat) ──────────────────────────
        window.addEventListener('messageSent', function() {
            applyMobileState(); // ← ضمان إضافي
            scrollToBottom();
            updateSendButton();
        });
        Livewire.on('messageSent', function() {
            applyMobileState(); // ← ضمان إضافي
            scrollToBottom();
            updateSendButton();
        });

        window.addEventListener('messagesLoaded', function(event) {
            const messagesArea = document.querySelector('.msg-body');
            if (!messagesArea) return;
            setTimeout(() => {
                const previousHeight = event.detail.height;
                const newHeight = messagesArea.scrollHeight;
                requestAnimationFrame(() => messagesArea.scrollTo({ top: newHeight - previousHeight, behavior: 'auto' }));
                Livewire.dispatch('resetLoadMoreTrigger', { height: messagesArea.scrollHeight });
            }, 20);
        });

        // ─── Story Viewer ────────────────────────────────────
        let progressTimer = null;
        const storyDuration = 10000;

        function clearProgress() {
            if (progressTimer) { clearTimeout(progressTimer); progressTimer = null; }
        }

        function startProgress(index) {
            clearProgress();
            const fill = document.getElementById('prog-' + index);
            if (!fill) return;
            fill.style.transition = 'none';
            fill.style.width = '0%';
            requestAnimationFrame(() => requestAnimationFrame(() => {
                fill.style.transition = `width ${storyDuration / 1000}s linear`;
                fill.style.width = '100%';
            }));
            progressTimer = setTimeout(() => Livewire.dispatch('nextViewerStory'), storyDuration);
        }

        Livewire.on('storyViewerOpened', (data) => setTimeout(() => startProgress(data.index), 80));
        Livewire.on('storyIndexChanged',  (data) => setTimeout(() => startProgress(data.index), 50));
        Livewire.on('storyViewerClosed',  clearProgress);

        document.addEventListener('keydown', function(e) {
            const viewer = document.getElementById('storyViewerOverlay');
            if (!viewer) return;
            if (e.key === 'ArrowRight') Livewire.dispatch('nextViewerStory');
            if (e.key === 'ArrowLeft')  Livewire.dispatch('prevViewerStory');
            if (e.key === 'Escape')     Livewire.dispatch('closeStoryViewer');
        });

    });
</script>
