<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        
        $request->validate([
            'country_code' => ['required', 'string', 'regex:/^\+\d+$/'],
            'phone' => ['required', 'string', 'min:6'],
        ]);


        $phoneUtil = PhoneNumberUtil::getInstance();

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
                    'expires_at' => now()->addMinutes(3),
                ]);
            }

            //$this->sendOtpSms($formatted, $otpCode);


            $request->session()->flash('auth_phone', $formatted);



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
}
