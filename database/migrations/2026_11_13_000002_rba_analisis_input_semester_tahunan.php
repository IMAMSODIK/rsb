<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semester 0 = nilai TAHUNAN.
     * - Target variabel & Realisasi tahunan variabel disimpan di semester 0.
     * - Prognosa variabel DIHITUNG (bukan disimpan), sama seperti level analisis.
     * - Semester 1/2 tetap didukung untuk rincian realisasi semesteran.
     */
    public function up(): void
    {
        Schema::table('rba_analisis_input', function (Blueprint $table) {
            $table->dropUnique('rba_anl_input_thn_smt_var_unique');
        });

        Schema::table('rba_analisis_input', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester')->default(0)->change();
        });

        Schema::table('rba_analisis_input', function (Blueprint $table) {
            $table->unique(['tahun_anggaran_id', 'semester', 'variabel_kode'], 'rba_anl_input_thn_smt_var_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rba_analisis_input', function (Blueprint $table) {
            $table->dropUnique('rba_anl_input_thn_smt_var_unique');
            $table->unsignedTinyInteger('semester')->default(1)->change();
            $table->unique(['tahun_anggaran_id', 'semester', 'variabel_kode'], 'rba_anl_input_thn_smt_var_unique');
        });
    }
};
