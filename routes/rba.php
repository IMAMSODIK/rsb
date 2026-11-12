<?php

use App\Http\Controllers\Rba\RbaAnalisisController;
use App\Http\Controllers\Rba\RbaDetailController;
use App\Http\Controllers\Rba\RbaIndikatorController;
use App\Http\Controllers\Rba\RbaJenisController;
use App\Http\Controllers\Rba\RbaKategoriController;
use App\Http\Controllers\Rba\RbaRealisasiController;
use App\Http\Controllers\Rba\RbaReportController;
use App\Http\Controllers\Rba\RbaTahunAnggaranController;
use App\Http\Controllers\Rba\RbaTargetController;
use App\Http\Middleware\Rba\EnsureRbaEnabled;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', EnsureRbaEnabled::class])->prefix('rba')->name('rba.')->group(function () {
    Route::middleware('admin')->prefix('master')->name('master.')->group(function () {
        Route::resource('jenis', RbaJenisController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('kategori', RbaKategoriController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('indikator', RbaIndikatorController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('detail', RbaDetailController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('tahun', RbaTahunAnggaranController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('tahun-current', [RbaTahunAnggaranController::class, 'current'])->name('tahun.current');
    });

    Route::prefix('transaksi')->name('transaksi.')->group(function () {
        Route::get('target', [RbaTargetController::class, 'index'])->name('target');
        Route::get('target/data', [RbaTargetController::class, 'data'])->name('target.data');
        Route::post('target/save', [RbaTargetController::class, 'save'])->name('target.save');
        Route::get('realisasi', [RbaRealisasiController::class, 'index'])->name('realisasi');
        Route::get('realisasi/data', [RbaRealisasiController::class, 'data'])->name('realisasi.data');
        Route::get('realisasi/detail-options', [RbaRealisasiController::class, 'detailOptions'])->name('realisasi.detail-options');
        Route::post('realisasi/save', [RbaRealisasiController::class, 'save'])->name('realisasi.save');
    });

    Route::prefix('analisis')->name('analisis.')->group(function () {
        Route::get('/', [RbaAnalisisController::class, 'index'])->name('index');
        Route::get('data', [RbaAnalisisController::class, 'data'])->name('data');
        Route::post('save', [RbaAnalisisController::class, 'save'])->name('save');
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [RbaReportController::class, 'index'])->name('index');
        Route::get('data', [RbaReportController::class, 'data'])->name('data');
        Route::get('export', [RbaReportController::class, 'export'])->name('export');
        Route::get('history', [RbaReportController::class, 'historyView'])->name('history');
        Route::get('history/data', [RbaReportController::class, 'history'])->name('history.data');
    });
});
