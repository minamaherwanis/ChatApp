<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Chat;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {
  Route::view('/login', 'login')->name('login');
  Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
  Route::view('/otp', 'otp')->name('otp');
  Route::post('/confirmotp', [AuthController::class, 'confirmotp'])->name('login.otp');
  Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('login.resend');
});


Route::middleware(['auth',  'profile.exists'])->group(function () {
  Route::view('/complete-profile', 'complete-profile')->name('profile.complete');
  Route::post('/save-profile', [AuthController::class, 'completeProfile'])->name('save.profile');
});


Route::middleware(['auth', 'has.profile'])->group(function () {
    Route::get('/', function () {
    return view('chat-list');
  })->name('/');
  Route::get('/home', function () {
    return view('chat-list');
  })->name('home');
});
