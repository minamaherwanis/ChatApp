@extends('layouts.app')

@section('title', 'Chats | ChatApp')

@section('styles')
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #07111c;
            color: #f0f6ff;
            font-family: 'Instrument Sans', Inter, system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ═══════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════ */
        .chat-container {
            display: flex;
            width: 100vw;
            height: 100dvh;
            height: 100vh;
            overflow: hidden;
            background: #07111c;
        }

        /* ═══════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════ */
        .chat-sidebar {
            width: 360px;
            min-width: 360px;
            max-width: 360px;
            background: #0d1a28;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex-shrink: 0;
            transition: transform 0.3s ease;
            z-index: 20;
        }

        /* ═══════════════════════════════════════
           HEADER
        ═══════════════════════════════════════ */
        .chat-header {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            background: #0d1a28;
            flex-shrink: 0;
        }

        .chat-header h1 {
            font-size: 1.45rem;
            font-weight: 700;
            color: #f0f6ff;
            letter-spacing: -0.02em;
        }

        .chat-header-actions {
            display: flex;
            gap: 0.4rem;
        }

        .icon-button {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(79, 162, 255, 0.1);
            border: 1px solid rgba(79, 162, 255, 0.15);
            color: #4fa2ff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .icon-button:hover {
            background: rgba(79, 162, 255, 0.22);
            border-color: rgba(79, 162, 255, 0.35);
            transform: scale(1.06);
        }

        /* ═══════════════════════════════════════
           SEARCH BOX
        ═══════════════════════════════════════ */
        .search-box {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            flex-shrink: 0;
        }

        .search-box input {
            width: 100%;
            padding: 0.65rem 1rem 0.65rem 2.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 24px;
            color: #f0f6ff;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.3)' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 0.85rem center;
        }

        .search-box input:focus {
            border-color: rgba(79, 162, 255, 0.35);
            background-color: rgba(79, 162, 255, 0.05);
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        /* ═══════════════════════════════════════
           CHAT LIST
        ═══════════════════════════════════════ */
        .chat-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .chat-list::-webkit-scrollbar { width: 4px; }
        .chat-list::-webkit-scrollbar-track { background: transparent; }
        .chat-list::-webkit-scrollbar-thumb { background: rgba(79,162,255,0.2); border-radius: 4px; }

        .chat-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 0.85rem;
            align-items: center;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.035);
            cursor: pointer;
            transition: background 0.18s ease;
            text-decoration: none;
            color: inherit;
            position: relative;
        }

        .chat-item:hover {
            background: rgba(79, 162, 255, 0.07);
        }

        .chat-item.active {
            background: rgba(79, 162, 255, 0.12);
            border-left: 3px solid #4fa2ff;
        }

        /* ═══════════════════════════════════════
           CHAT AVATAR
        ═══════════════════════════════════════ */
        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4fa2ff 0%, #1e5ba5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 700;
            color: #fff;
            font-size: 1.1rem;
            overflow: hidden;
        }

        .chat-preview {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            min-width: 0;
        }

        .chat-name {
            font-weight: 600;
            font-size: 0.92rem;
            color: #f0f6ff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-message {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.5);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-time {
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.38);
            flex-shrink: 0;
            white-space: nowrap;
        }

        /* ═══════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════ */
        .chat-content {
            flex: 1;
            min-width: 0;
            background: #07111c;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ═══════════════════════════════════════
           EMPTY STATE
        ═══════════════════════════════════════ */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            text-align: center;
            max-width: 360px;
            padding: 2rem;
        }

        .empty-state-icon {
            font-size: 3.5rem;
            opacity: 0.45;
            margin-bottom: 0.5rem;
        }

        .empty-state h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #f0f6ff;
        }

        .empty-state p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* ═══════════════════════════════════════
           RESPONSIVE — Tablet (≤ 900px)
        ═══════════════════════════════════════ */
        @media (max-width: 900px) {
            .chat-sidebar {
                width: 300px;
                min-width: 300px;
                max-width: 300px;
            }
        }

        /* ═══════════════════════════════════════
           RESPONSIVE — Mobile (≤ 767px)
        ═══════════════════════════════════════ */
        @media (max-width: 767px) {
            .chat-container {
                position: relative;
            }

            .chat-sidebar {
                position: absolute;
                inset: 0;
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                height: 100%;
                border-right: none;
                transform: translateX(0);
            }

            .chat-sidebar.hidden-mobile {
                transform: translateX(-100%);
                pointer-events: none;
            }

            .chat-content {
                position: absolute;
                inset: 0;
                width: 100%;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }

            .chat-content.mobile-active {
                transform: translateX(0) !important;
            }

            .chat-avatar {
                width: 46px;
                height: 46px;
                font-size: 1rem;
            }

            .chat-item {
                padding: 0.8rem 0.9rem;
            }
        }
    </style>
@endsection

@section('content')
 <livewire:chat />
@endsection
