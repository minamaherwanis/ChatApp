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
<style>
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
