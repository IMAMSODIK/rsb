<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_kinerja_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_kinerja_layanan_id')
                ->constrained('target_kinerja_layanan')
                ->cascadeOnDelete();
            $table->unique('target_kinerja_layanan_id', 'realisasi_layanan_target_unique');
            $table->json('realisasi_tahunan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });

        Schema::create('realisasi_kinerja_layanan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_id')
                ->constrained('realisasi_kinerja_layanan')
                ->cascadeOnDelete();
            $table->string('nama_asli');
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('ukuran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_kinerja_layanan_dokumen');
        Schema::dropIfExists('realisasi_kinerja_layanan');
    }
};
