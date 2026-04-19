@extends('layouts.app')

@section('title', 'Verify Code | ChatMU')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY') }}"></script>

    <style>
        .otp-panel {
            display: grid;
            gap: 1.5rem;
        }

        .otp-intro {
            color: var(--muted);
            line-height: 1.8;
            max-width: 42ch;
        }

        .otp-code-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(3rem, 1fr));
            gap: 0.8rem;
        }

        .otp-input {
            width: 100%;
            min-height: 4.25rem;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: #15263d;
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .otp-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(79, 162, 255, 0.18);
        }

        .otp-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .otp-secondary {
            background: transparent;
            border: none;
            color: var(--accent);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            font-size: 0.95rem;
        }

        .otp-secondary:disabled {
            color: rgba(79, 162, 255, 0.45);
            cursor: not-allowed;
        }

        .otp-status {
            color: var(--muted);
            font-size: 0.95rem;
        }

        @media (max-width: 520px) {
            .otp-code-grid {
                gap: 0.5rem;
            }
            .otp-input {
                min-height: 3.75rem;
                font-size: 1.25rem;
                border-radius: 14px;
            }
        }

        @media (max-width: 420px) {
            .otp-code-grid {
                gap: 0.4rem;
            }
            .otp-input {
                min-height: 3.5rem;
                font-size: 1.15rem;
                border-radius: 12px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="login-shell">
        <section class="login-panel otp-panel">
            <div class="brand">
                <span class="brand-mark">Chat</span>
                <span class="brand-name">MU</span>
            </div>

            <div>
                <h1>Enter verification code</h1>
                <p class="lead otp-intro">We sent a 6-digit code to
                    <strong>{{ session('auth_phone', '+20 1234 5678') }}</strong>. Enter it below to continue to your chat.
                </p>
            </div>

            <form class="login-form" action="{{ route('login.otp') }}" method="POST" id="otpForm">
                @csrf
                {{-- <input type="hidden" name="g-recaptcha-response" id="recaptcha_token"> --}}        
                <input type="hidden" name="code" id="otp_code">
                <div class="otp-code-grid">
                    <input class="otp-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                        autocomplete="one-time-code" />
                    <input class="otp-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                        autocomplete="one-time-code" />
                    <input class="otp-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                        autocomplete="one-time-code" />
                    <input class="otp-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                        autocomplete="one-time-code" />
                    <input class="otp-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                        autocomplete="one-time-code" />
                    <input class="otp-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                        autocomplete="one-time-code" />
                </div>

                @error('code')
                    <p class="help-text" style="color: #ff6b6b; margin-top: 0.5rem;">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn-primary">Verify code</button>
            </form>

            <div class="otp-actions">
                <a class="otp-secondary" href="{{ route('login') }}">Change number</a>
                <button type="button" id="resendButton" class="otp-secondary" disabled>Resend code in 30s</button>
            </div>
            <p class="otp-status" id="otpStatus">You can request a new code after the timer ends.</p>
        </section>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.otp-input');
    const form = document.getElementById('otpForm');
    const hiddenCode = document.getElementById('otp_code');
    const resendButton = document.getElementById('resendButton');
    const otpStatus = document.getElementById('otpStatus');

    const TIMER_KEY = 'otp_timer_end';
    let timer;

    // ===== Timer =====
    function getRemainingTime() {
        const endTime = localStorage.getItem(TIMER_KEY);
        if (!endTime) return 0;
        const remaining = Math.ceil((Number(endTime) - Date.now()) / 1000);
        return remaining > 0 ? remaining : 0;
    }

    function updateTimer() {
        const remaining = getRemainingTime();

        if (remaining > 0) {
            resendButton.textContent = `Resend code in ${remaining}s`;
            resendButton.disabled = true;
            otpStatus.textContent = 'Please wait before requesting a new code.';
        } else {
            resendButton.textContent = 'Resend code';
            resendButton.disabled = false;
            otpStatus.textContent = 'You can resend the code now.';
            localStorage.removeItem(TIMER_KEY);
        }
    }

    function startTimer(seconds = 30) {
        const endTime = Date.now() + seconds * 1000;
        localStorage.setItem(TIMER_KEY, endTime);
        updateTimer();

        clearInterval(timer);
        timer = setInterval(updateTimer, 1000);
    }

    // start on load if exists
    if (getRemainingTime() > 0) {
        timer = setInterval(updateTimer, 1000);
        updateTimer();
    } else {
        updateTimer();
    }

    resendButton.addEventListener('click', () => {
        if (resendButton.disabled) return;
        startTimer(30);
        otpStatus.textContent = 'A new code has been sent. Check your messages.';
    });

    // ===== OTP Inputs =====
    inputs.forEach((input, index) => {

        input.addEventListener('input', () => {
            const value = input.value.replace(/\D/g, '');
            input.value = value;

            if (value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            // auto submit
            if (value && index === inputs.length - 1) {
                submitOtp();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', (e) => {
            const paste = e.clipboardData.getData('text').replace(/\D/g, '');
            if (paste.length === 6) {
                inputs.forEach((inputField, i) => {
                    inputField.value = paste[i] || '';
                });
                submitOtp();
            }
        });
    });

    function submitOtp() {
        let code = '';
        inputs.forEach(input => code += input.value);

        if (code.length === 6) {
            hiddenCode.value = code;
            form.submit();
        }
    }

    // ===== Form Submit =====
    form.addEventListener('submit', function(e) {
        let code = '';
        inputs.forEach(input => code += input.value);

        if (code.length < 6) {
            e.preventDefault();
            otpStatus.textContent = 'Enter full verification code';
            return;
        }

        hiddenCode.value = code;
    });

    // ===== reCAPTCHA =====
    // grecaptcha.ready(function() {
    //     grecaptcha.execute("{{ env('RECAPTCHA_SITE_KEY') }}", { action: 'submit' })
    //         .then(function(token) {
    //             document.getElementById('recaptcha_token').value = token;
    //         });
    // });
});
</script>
@endsection
