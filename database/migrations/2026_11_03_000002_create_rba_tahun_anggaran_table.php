<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rba_tahun_anggaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->unique();
            $table->string('status', 20)->default('OPEN');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rba_tahun_anggaran');
    }
};
