@extends('layouts.app')

@section('title', 'Login | ChatApp')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.min.css" integrity="sha512-2whPhwGt6qkTnssoyq_WKMfVH6NavNdB0hXLgtuZpHyYk5IuP33i8DUSv/0Vx4Y2Uj088Lr4Pff7xUOuM8erZA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')
    <div class="login-shell">
        <section class="login-panel">
            <div class="brand">
                <span class="brand-mark">Chat</span>
                <span class="brand-name">Flow</span>
            </div>
            <h1>Login with your phone number</h1>
            <p class="lead">A calm and modern sign-in experience optimized for quick access.</p>

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

            <p class="help-text">We’ll send a one-time verification code to your device so you can join the chat quickly.</p>
        </section>

        <aside class="login-aside">
            <div class="aside-overlay"></div>
            <div class="aside-copy">
                <p class="eyebrow">Welcome back</p>
                <h2>Keep conversations flowing easily</h2>
                <p>Enter your phone number to access the chat experience with a soft, modern interface designed for long sessions and clear readability.</p>
            </div>
        </aside>
    </div>
@endsection