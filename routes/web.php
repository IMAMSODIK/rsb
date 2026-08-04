<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IkuLayananController;
use App\Http\Controllers\JenisLayananController;
use App\Http\Controllers\KategoriLayananController;
use App\Http\Controllers\PeriodeRsbController;
use App\Http\Controllers\SasaranProgramController;
use App\Http\Controllers\SasaranStrategisController;
use App\Http\Controllers\SliderItemController;
use App\Http\Controllers\TujuanRsbController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/home', '/dashboard');
Route::redirect('/', '/dashboard');

// Auth routes (hanya untuk user yang sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('unit-kerja', UnitKerjaController::class)->except(['create', 'edit', 'show']);
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::resource('jenis-layanan', JenisLayananController::class)->except(['create', 'edit', 'show']);
        Route::resource('kategori-layanan', KategoriLayananController::class)->except(['create', 'edit', 'show']);
        Route::resource('iku-layanan', IkuLayananController::class)->except(['create', 'edit', 'show']);
        Route::resource('tujuan-rsb', TujuanRsbController::class)->except(['create', 'edit', 'show']);
        Route::resource('sasaran-program', SasaranProgramController::class)->except(['create', 'edit', 'show']);
        Route::resource('sasaran-strategis', SasaranStrategisController::class)->except(['create', 'edit', 'show']);
        Route::resource('periode-rsb', PeriodeRsbController::class)->except(['create', 'edit', 'show']);
        Route::resource('settings', SliderItemController::class)->except(['create', 'edit', 'show']);
    });
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
