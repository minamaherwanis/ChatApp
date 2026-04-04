@extends('layouts.app')

@section('title', 'Chats | ChatApp')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(180deg, #08131f 0%, #0a1825 100%);
            min-height: 100vh;
        }

        .chat-container {
            display: grid;
            grid-template-columns: 360px 1fr;
            height: 100vh;
            background: #08131f;
        }

        .chat-sidebar {
            background: #0f1b2b;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .chat-header h1 {
            font-size: 1.8rem;
            margin: 0;
        }

        .chat-header-actions {
            display: flex;
            gap: 0.5rem;
        }

        .icon-button {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(79, 162, 255, 0.12);
            border: none;
            color: #4fa2ff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }

        .icon-button:hover {
            background: rgba(79, 162, 255, 0.2);
        }

        .search-box {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #15263d;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            color: #f8fbff;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-box input:focus {
            border-color: rgba(79, 162, 255, 0.4);
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .chat-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            cursor: pointer;
            transition: background 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .chat-item:hover {
            background: rgba(79, 162, 255, 0.08);
        }

        .chat-item.active {
            background: rgba(79, 162, 255, 0.14);
            border-left: 3px solid #4fa2ff;
            padding-left: calc(1rem - 3px);
        }

        .chat-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4fa2ff 0%, #1e5ba5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 700;
            color: #f8fbff;
            font-size: 1.25rem;
        }

        .chat-preview {
            display: grid;
            gap: 0.25rem;
            min-width: 0;
        }

        .chat-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #f8fbff;
        }

        .chat-message {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-time {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.45);
            flex-shrink: 0;
        }

        .chat-content {
            flex: 1;
            background: #08131f;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            padding: 2rem;
        }

        .empty-state {
            text-align: center;
            max-width: 400px;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 1rem;
        }

        @media (max-width: 900px) {
            .chat-container {
                grid-template-columns: 1fr;
            }

            .chat-sidebar {
                position: absolute;
                width: 320px;
                height: 100%;
                z-index: 10;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            }

            .chat-content {
                display: none;
            }
        }

        /* Scrollbar styling */
        .chat-list::-webkit-scrollbar {
            width: 6px;
        }

        .chat-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-list::-webkit-scrollbar-thumb {
            background: rgba(79, 162, 255, 0.25);
            border-radius: 3px;
        }

        .chat-list::-webkit-scrollbar-thumb:hover {
            background: rgba(79, 162, 255, 0.4);
        }
    </style>
@endsection

@section('content')
    <div class="chat-container">
        <!-- Sidebar -->
        <aside class="chat-sidebar">
            <div class="chat-header">
                <h1>Chats</h1>
                <div class="chat-header-actions">
                    <button class="icon-button" title="Start new chat">✏️</button>
                    <button class="icon-button" title="More options">⋯</button>
                </div>
            </div>

            <div class="search-box">
                <input type="text" placeholder="Search chats..." id="searchInput">
            </div>

            <div class="chat-list">
                <!-- Sample chat items -->
                <a href="#" class="chat-item active">
                    <div class="chat-avatar">JD</div>
                    <div class="chat-preview">
                        <div class="chat-name">John Doe</div>
                        <div class="chat-message">That sounds great! Let's meet...</div>
                    </div>
                    <div class="chat-time">2m</div>
                </a>

                <a href="#" class="chat-item">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, #ff6b6b 0%, #ff5252 100%);">SD</div>
                    <div class="chat-preview">
                        <div class="chat-name">Sarah Davis</div>
                        <div class="chat-message">See you tomorrow!</div>
                    </div>
                    <div class="chat-time">1h</div>
                </a>

                <a href="#" class="chat-item">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);">MR</div>
                    <div class="chat-preview">
                        <div class="chat-name">Mike Roberts</div>
                        <div class="chat-message">Thanks for the update!</div>
                    </div>
                    <div class="chat-time">3h</div>
                </a>

                <a href="#" class="chat-item">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, #ffd43b 0%, #fab005 100%);">ET</div>
                    <div class="chat-preview">
                        <div class="chat-name">Emma Thompson</div>
                        <div class="chat-message">Ready for the meeting?</div>
                    </div>
                    <div class="chat-time">5h</div>
                </a>

                <a href="#" class="chat-item">
                    <div class="chat-avatar" style="background: linear-gradient(135deg, #a78bfa 0%, #9370db 100%);">CJ</div>
                    <div class="chat-preview">
                        <div class="chat-name">Chris Johnson</div>
                        <div class="chat-message">Perfect! Looking forward to it</div>
                    </div>
                    <div class="chat-time">1d</div>
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <div class="chat-content">
            <div class="empty-state">
                <div class="empty-state-icon">💬</div>
                <h2>Select a chat</h2>
                <p>Choose a conversation from the sidebar to start messaging. Or create a new chat to connect with friends.</p>
            </div>
        </div>
    </div>
@endsection
