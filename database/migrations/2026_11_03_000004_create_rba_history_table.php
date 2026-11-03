<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rba_history', function (Blueprint $table) {
            $table->id();
            $table->string('aksi', 20);
            $table->string('tabel_referensi', 100);
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->json('data_sebelum')->nullable();
            $table->json('data_sesudah')->nullable();
            $table->text('alasan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rba_history');
    }
};
