<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;



Route::view('/login', 'login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::view('/otp', 'otp')->name('otp');
