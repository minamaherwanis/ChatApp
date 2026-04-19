@extends('layouts.app')

@section('title', 'Login | ChatMU')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.min.css" integrity="sha512-2whPhwGt6qkTnssoyq_WKMfVH6NavNdB0hXLgtuZpHyYk5IuP33i8DUSv/0Vx4Y2Uj088Lr4Pff7xUOuM8erZA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')
    <div class="login-shell">
        <section class="login-panel">
            <div class="brand">
                <span class="brand-mark">Chat</span>
                <span class="brand-name">MU</span>
            </div>

            <h1>Login with your phone number</h1>
            <p class="lead">Your conversations, instant and always alive. Sign in to keep the pulse going.</p>
                        <div class="encryption-badge" style="margin: 1rem 0 1rem 0; padding: 1rem; background: linear-gradient(135deg, rgba(34,197,94,0.05) 0%, rgba(34,197,94,0.02) 100%); border: 1px solid rgba(34,197,94,0.2); border-radius: 8px; display: flex; align-items: flex-start; gap: 0.75rem;">
                <div>
                    <p style="font-weight: 600; font-size: 0.8rem; margin: 0 0 0.25rem 0; color: #22c55e;">🔐 Messages are encrypted for security</p>

                </div>
            </div>
            <form class="login-form" action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="phone-row">
                    <label class="input-group">
                        <span>Country</span>
                      
                    @include('components.country')
                    </label>

                    <label class="input-group">
                        <span>Phone number</span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="1234 5678" required>
                      
                    </label>

                </div>
  @error('phone')
                            <p class="help-text" style="color: #ff6b6b; margin-top: 0.5rem;">{{ $message }}</p>
                        @enderror
                <button type="submit" class="btn-primary">Continue with phone</button>
            </form>

            <p class="help-text">We'll send a one-time code to your phone &mdash; quick, secure, and hassle-free.</p>
        </section>

        <aside class="login-aside">
            <div class="aside-overlay"></div>
            <div class="aside-copy">
                <p class="eyebrow">Welcome back 👋</p>
                <h2>Real-time chats, zero delays</h2>
                <p>ChatMU keeps you connected with the people that matter. Fast, clean, and always in sync.</p>
            </div>
        </aside>
    </div>
@endsection