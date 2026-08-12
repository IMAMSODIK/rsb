<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_kinerja_iku_rsb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_rsb_id')
                ->constrained('periode_rsb')
                ->cascadeOnDelete();
            $table->foreignId('iku_kinerja_layanan_id')
                ->constrained('iku_kinerja_layanan')
                ->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')
                ->constrained('unit_kerja')
                ->cascadeOnDelete();
            $table->json('target_tahunan')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(
                ['periode_rsb_id', 'iku_kinerja_layanan_id', 'unit_kerja_id'],
                'target_iku_periode_iku_unit_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_kinerja_iku_rsb');
    }
};
