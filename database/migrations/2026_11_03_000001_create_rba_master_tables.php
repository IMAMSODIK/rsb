<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rba_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis', 50)->unique();
            $table->string('uraian');
            $table->timestamps();
        });

        Schema::create('rba_kategori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_id')->constrained('rba_jenis')->cascadeOnDelete();
            $table->string('kode_kategori', 50)->unique();
            $table->string('uraian');
            $table->timestamps();
        });

        Schema::create('rba_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('rba_kategori')->cascadeOnDelete();
            $table->string('kode_indikator', 50)->unique();
            $table->string('uraian');
            $table->timestamps();
        });

        Schema::create('rba_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('rba_indikator')->cascadeOnDelete();
            $table->string('kode_detail', 50)->unique();
            $table->string('uraian');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rba_detail');
        Schema::dropIfExists('rba_indikator');
        Schema::dropIfExists('rba_kategori');
        Schema::dropIfExists('rba_jenis');
    }
};
