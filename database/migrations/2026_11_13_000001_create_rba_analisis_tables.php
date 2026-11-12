<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel generik agar analisis baru TIDAK butuh migrasi baru:
     * - rba_analisis_input  : data mentah per tahun+semester, kolom variabel_kode bebas
     *                         (total_mahasiswa, jlh_dosen_pns, ... , variabel baru nanti).
     * - rba_analisis_target : target per tahun per kode analisis
     *                         (ROLSM-Total hari ini, kode analisis baru nanti).
     * Realisasi & prognosa TIDAK disimpan — selalu dihitung dari data mentah
     * via RbaAnalisisCalculator (sesuai flow: "realisasi dihitung dari data yang diinput").
     */
    public function up(): void
    {
        Schema::create('rba_analisis_input', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('rba_tahun_anggaran');
            $table->unsignedTinyInteger('semester');
            $table->string('variabel_kode', 50);
            $table->decimal('nilai', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['tahun_anggaran_id', 'semester', 'variabel_kode'], 'rba_anl_input_thn_smt_var_unique');
            $table->index(['tahun_anggaran_id', 'semester'], 'rba_anl_input_thn_smt_idx');
        });

        Schema::create('rba_analisis_target', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('rba_tahun_anggaran');
            $table->string('analisis_kode', 50);
            $table->decimal('target', 20, 4)->default(0);
            $table->timestamps();

            $table->unique(['tahun_anggaran_id', 'analisis_kode'], 'rba_anl_target_thn_kode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rba_analisis_target');
        Schema::dropIfExists('rba_analisis_input');
    }
};
