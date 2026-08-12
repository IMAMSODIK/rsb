<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IkuLayananController;
use App\Http\Controllers\JenisLayananController;
use App\Http\Controllers\KategoriLayananController;
use App\Http\Controllers\PeriodeRsbController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SasaranProgramController;
use App\Http\Controllers\SasaranStrategisController;
use App\Http\Controllers\SliderItemController;
use App\Http\Controllers\TujuanRsbController;
use App\Http\Controllers\TargetKinerjaLayananController;
use App\Http\Controllers\TargetKinerjaIkuRsbController;
use App\Http\Controllers\RealisasiKinerjaLayananController;
use App\Http\Controllers\RealisasiKinerjaIkuRsbController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/home', '/dashboard');
Route::redirect('/', '/dashboard');

// Auth routes (hanya untuk user yang sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Master Data
    Route::middleware('admin')->prefix('master')->name('master.')->group(function () {
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

    // Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('target-kinerja-layanan', [TargetKinerjaLayananController::class, 'index'])
            ->name('target-kinerja-layanan');
        Route::get('target-kinerja-layanan/data', [TargetKinerjaLayananController::class, 'data'])
            ->name('target-kinerja-layanan.data');
        Route::post('target-kinerja-layanan/draft', [TargetKinerjaLayananController::class, 'saveDraft'])
            ->name('target-kinerja-layanan.draft');
        Route::get('realisasi-kinerja-layanan', [RealisasiKinerjaLayananController::class, 'index'])
            ->name('realisasi-kinerja-layanan');
        Route::get('realisasi-kinerja-layanan/data', [RealisasiKinerjaLayananController::class, 'data'])
            ->name('realisasi-kinerja-layanan.data');
        Route::post('realisasi-kinerja-layanan/draft', [RealisasiKinerjaLayananController::class, 'saveDraft'])
            ->name('realisasi-kinerja-layanan.draft');
        Route::post('realisasi-kinerja-layanan/documents', [RealisasiKinerjaLayananController::class, 'uploadDocuments'])
            ->name('realisasi-kinerja-layanan.documents.store');
        Route::delete('realisasi-kinerja-layanan/documents/{document}', [RealisasiKinerjaLayananController::class, 'deleteDocument'])
            ->name('realisasi-kinerja-layanan.documents.destroy');
        Route::get('target-kinerja-iku-rsb', [TargetKinerjaIkuRsbController::class, 'index'])
            ->name('target-kinerja-iku-rsb');
        Route::get('target-kinerja-iku-rsb/data', [TargetKinerjaIkuRsbController::class, 'data'])
            ->name('target-kinerja-iku-rsb.data');
        Route::post('target-kinerja-iku-rsb/draft', [TargetKinerjaIkuRsbController::class, 'saveDraft'])
            ->name('target-kinerja-iku-rsb.draft');
        Route::get('realisasi-kinerja-iku-rsb', [RealisasiKinerjaIkuRsbController::class, 'index'])
            ->name('realisasi-kinerja-iku-rsb');
        Route::get('realisasi-kinerja-iku-rsb/data', [RealisasiKinerjaIkuRsbController::class, 'data'])
            ->name('realisasi-kinerja-iku-rsb.data');
        Route::post('realisasi-kinerja-iku-rsb/draft', [RealisasiKinerjaIkuRsbController::class, 'saveDraft'])
            ->name('realisasi-kinerja-iku-rsb.draft');
        Route::post('realisasi-kinerja-iku-rsb/documents', [RealisasiKinerjaIkuRsbController::class, 'uploadDocuments'])
            ->name('realisasi-kinerja-iku-rsb.documents.store');
        Route::delete('realisasi-kinerja-iku-rsb/documents/{document}', [RealisasiKinerjaIkuRsbController::class, 'deleteDocument'])
            ->name('realisasi-kinerja-iku-rsb.documents.destroy');
        Route::get('laporan-capaian-kinerja-layanan', [ReportController::class, 'layanan'])
            ->name('laporan-capaian-kinerja-layanan');
        Route::get('laporan-capaian-kinerja-layanan/data', [ReportController::class, 'dataLayanan'])
            ->name('laporan-capaian-kinerja-layanan.data');
        Route::get('laporan-capaian-kinerja-layanan/export/{format}', [ReportController::class, 'exportLayanan'])
            ->where('format', 'excel')
            ->name('laporan-capaian-kinerja-layanan.export');
        Route::get('laporan-capaian-kinerja-iku', [ReportController::class, 'iku'])
            ->name('laporan-capaian-kinerja-iku');
        Route::get('laporan-capaian-kinerja-iku/data', [ReportController::class, 'dataIku'])
            ->name('laporan-capaian-kinerja-iku.data');
        Route::get('laporan-capaian-kinerja-iku/export/{format}', [ReportController::class, 'exportIku'])
            ->where('format', 'excel')
            ->name('laporan-capaian-kinerja-iku.export');
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
