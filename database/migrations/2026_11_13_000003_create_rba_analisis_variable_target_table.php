<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Target TAHUNAN per variabel disimpan di tabel sendiri agar tidak
     * tercampur dengan Realisasi tahunan (semester 0 di rba_analisis_input).
     * Realisasi & Prognosa variabel DIHITUNG dari input realisasi.
     */
    public function up(): void
    {
        Schema::create('rba_analisis_variable_target', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('rba_tahun_anggaran');
            $table->string('variabel_kode', 50);
            $table->decimal('target', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['tahun_anggaran_id', 'variabel_kode'], 'rba_anl_vartarget_thn_kode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rba_analisis_variable_target');
    }
};
