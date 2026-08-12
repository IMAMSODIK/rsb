<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_kinerja_iku_rsb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_kinerja_iku_rsb_id')
                ->constrained('target_kinerja_iku_rsb')
                ->cascadeOnDelete();
            $table->json('realisasi_tahunan')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->unique('target_kinerja_iku_rsb_id', 'realisasi_iku_target_unique');
        });

        Schema::create('realisasi_kinerja_iku_rsb_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_id')
                ->constrained('realisasi_kinerja_iku_rsb')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('semester');
            $table->string('nama_asli');
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('ukuran')->nullable();
            $table->timestamps();
            $table->index(['tahun', 'semester'], 'realisasi_iku_dokumen_tahun_semester_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_kinerja_iku_rsb_dokumen');
        Schema::dropIfExists('realisasi_kinerja_iku_rsb');
    }
};
