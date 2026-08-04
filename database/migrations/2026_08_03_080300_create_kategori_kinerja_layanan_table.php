<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_kinerja_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')
                ->constrained('jenis_kinerja_layanan')
                ->cascadeOnDelete();
            $table->string('kode_kategori')->unique();
            $table->text('uraian');
            $table->foreignId('unit_kerja_id')
                ->nullable()
                ->constrained('unit_kerja')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_kinerja_layanan');
    }
};
