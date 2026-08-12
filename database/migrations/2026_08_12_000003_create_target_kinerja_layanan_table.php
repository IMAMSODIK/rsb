<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_kinerja_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_rsb_id')
                ->constrained('periode_rsb')
                ->cascadeOnDelete();
            $table->foreignId('kategori_id')
                ->constrained('kategori_kinerja_layanan')
                ->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')
                ->constrained('unit_kerja')
                ->cascadeOnDelete();
            $table->json('target_tahunan')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(
                ['periode_rsb_id', 'kategori_id', 'unit_kerja_id'],
                'target_layanan_periode_kategori_unit_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_kinerja_layanan');
    }
};
