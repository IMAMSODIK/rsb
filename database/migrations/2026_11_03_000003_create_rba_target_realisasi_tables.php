<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rba_target', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_id')->constrained('rba_detail')->cascadeOnDelete();
            $table->foreignId('tahun_anggaran_id')->constrained('rba_tahun_anggaran');
            $table->decimal('nilai', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['detail_id', 'tahun_anggaran_id'], 'rba_target_detail_tahun_unique');
        });

        Schema::create('rba_realisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained('rba_target')->cascadeOnDelete();
            $table->unique('target_id', 'rba_realisasi_target_unique');
            $table->decimal('semester_1', 20, 2)->nullable();
            $table->decimal('semester_2', 20, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rba_realisasi');
        Schema::dropIfExists('rba_target');
    }
};
