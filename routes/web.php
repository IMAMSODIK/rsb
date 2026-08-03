<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/home', '/dashboard');
Route::redirect('/', '/dashboard');

// Auth routes (hanya untuk user yang sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Guest routes (hanya untuk user yang belum login)
Route::middleware(['guest'])->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');

    // Lupa Password - Kirim OTP
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'sendOtp'])->name('send-otp');

    // Verifikasi OTP
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verify-otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');

    // Reset Password
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('reset-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('update-password');
});
