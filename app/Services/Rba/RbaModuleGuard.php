<?php

namespace App\Services\Rba;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RbaModuleGuard
{
    /**
     * Tabel yang wajib ada agar modul RBA bisa berjalan.
     *
     * @var list<string>
     */
    private const TABLES = [
        'rba_jenis',
        'rba_kategori',
        'rba_indikator',
        'rba_detail',
        'rba_tahun_anggaran',
        'rba_target',
        'rba_realisasi',
        'rba_analisis_input',
        'rba_analisis_target',
        'rba_analisis_variable_target',
        'rba_history',
    ];

    /**
     * Pastikan semua tabel RBA sudah dimigrasi.
     * Jika belum, hentikan dengan pesan eksplisit agar tidak
     * terlempar diam-diam ke halaman lain via redirect()->back().
     */
    public static function ensureInstalled(): void
    {
        $missing = [];

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                $missing[] = $table;
            }
        }

        if (! empty($missing)) {
            Log::error('RbaModuleGuard: missing tables', ['missing' => $missing]);

            abort(503, 'Modul RBA belum terinstal. Tabel yang hilang: ' . implode(', ', $missing) . '. Jalankan: php artisan migrate --force');
        }

        Log::debug('RbaModuleGuard: all tables present', ['tables' => self::TABLES]);
    }
}
