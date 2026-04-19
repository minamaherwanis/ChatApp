<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{

    public function login(Request $request)
    {

        $request->validate([
            'country_code' => ['required', 'string', 'regex:/^\+\d+$/'],
            'phone' => ['required', 'string', 'min:6'],
        ]);


        $phoneUtil = PhoneNumberUtil::getInstance();
        $this->throttleOtp($request);
        try {
            $fullPhone = $request->country_code . $request->phone;

            $number = $phoneUtil->parse($fullPhone, null);

            if (!$phoneUtil->isValidNumber($number)) {
                return back()
                    ->withErrors(['phone' => 'Phone number is not valid'])
                    ->withInput();
            }

            $formatted = $phoneUtil->format($number, PhoneNumberFormat::E164);
            $otpCode = rand(100000, 999999);

            $otp = Otp::where('phone', $formatted)->first();

            if ($otp) {
                $otp->update([
                    'code' => $otpCode,
                    'expires_at' => now()->addMinutes(3),
                    'is_used' => false,
                    'attempts' => 0,
                ]);
            } else {
                Otp::create([
                    'phone' => $formatted,
                    'code' => $otpCode,
                    'is_used' => false,
                    'expires_at' => now()->addMinutes(3),
                ]);
            }

            $this->sendOtpSms($formatted, $otpCode);


            $request->session()->put('auth_phone', $formatted);



            return redirect()->route('otp');
        } catch (NumberParseException $e) {
            return back()
                ->withErrors(['phone' => 'Phone number format is invalid'])
                ->withInput();
        }
    }

    private function sendOtpSms($phone, $otp)
    {
        $response = Http::withBasicAuth(
            env('VONAGE_API_KEY'),
            env('VONAGE_API_SECRET')
        )->post('https://api.nexmo.com/v1/messages', [
            "to" => $phone,
            "from" => "ChatAPP",
            "channel" => "sms",
            "message_type" => "text",
            "text" => "Your OTP is: " . $otp
        ]);

        return $response->successful();
    }

    public function confirmotp(Request $request)
    {


        $this->throttleOtp($request);
        $otp = Otp::where('phone', session('auth_phone'))->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'OTP not found']);
        }


        if ($otp->attempts >= 5) {
            return back()->withErrors(['code' => 'Too many attempts']);
        }

        if (
            $otp->code != $request->code ||
            $otp->expires_at < now() ||
            $otp->is_used
        ) {
            // زيادة المحاولات
            $otp->increment('attempts');

            return back()->withErrors(['code' => 'Invalid or expired OTP']);
        }

        // نجاح
        $otp->update([
            'is_used' => true,
            'attempts' => 0
        ]);
        if (!$user = User::where('phone', session('auth_phone'))->first()) {
            $user = User::create(
                ['phone' => session('auth_phone')],
                ['phone_verified_at' => now()]

            );
        }
        Auth::login($user);
        return redirect()->route('home');
    }

    public function resendOtp(Request $request)
    {
        if (!session('auth_phone')) {
            return response()->json(['message' => 'Unable to resend OTP. Please start again.'], 422);
        }

        $this->throttleOtp($request);

        $otpCode = rand(100000, 999999);
        $otp = Otp::where('phone', session('auth_phone'))->first();

        if ($otp) {
            $otp->update([
                'code' => $otpCode,
                'expires_at' => now()->addMinutes(3),
                'is_used' => false,
                'attempts' => 0,
            ]);
        } else {
            Otp::create([
                'phone' => session('auth_phone'),
                'code' => $otpCode,
                'expires_at' => now()->addMinutes(3),
            ]);
        }

        //$this->sendOtpSms(session('auth_phone'), $otpCode);

        return response()->json(['message' => 'OTP resent successfully']);
    }

    private function throttleOtp(Request $request)
    {
        $key = 'otp_attempts_' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Too many attempts. Please try again. after 5 minutes',
                'phone' => 'Too many attempts. Please try again. after 5 minutes',
            ]);
        }

        RateLimiter::hit($key, 300);
    }


    public function completeProfile(Request $request)
    {
        $firstMsg = <<<MSG
Hello 👋

I'm Mena Maher, a backend developer focused on building secure and efficient systems ⚙️
I work on APIs, databases, and the logic that powers applications 💻

This is an automated message sent on your first login 🤖

Welcome to the chat! Your conversations are protected with secure encryption 🔐

Feel free to explore and connect with others. Check out my portfolio 👇
🌐 https://minamaherwanis.github.io/Portfolio/

Need help? I'm always here 😊

Enjoy 🚀
MSG;


        $user = Auth::user();

        // تأكد إن في Profile مرتبط بالـ User
        $profile = $user->profile()->firstOrNew([
            'user_id' => $user->id
        ]);

        // Validation
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'username'   => 'required|string|max:255|unique:profiles,username,' . ($profile->id ?? 'NULL'),
            'email'      => 'required|email|unique:profiles,email,' . ($profile->id ?? 'NULL'),
            'bio'        => 'nullable|string|max:1000',
            'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // فارغ مسموح
        ]);

        // دمج first_name و last_name في عمود name
        $profile->name     = $request->first_name . ' ' . $request->last_name;
        $profile->username = $request->username;
        $profile->email    = $request->email;
        $profile->bio      = $request->bio;

        // رفع الصورة (لو مش متحملة، يخليها فارغة)
        if ($request->hasFile('photo')) {
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $profile->avatar = $request->file('photo')->store('avatars', 'public');
        }

        $profile->save();

        $chat = Chat::create([
            'user_one_id' => 1,
            'user_two_id' => $profile->user_id,
            'last_message_at'  => now(),
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'sender_id' => 1,
            'content' => $firstMsg,
            'is_read' => false
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}
