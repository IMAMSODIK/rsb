<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iku_kinerja_layanan', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');
        });
    }

    public function down(): void
    {
        Schema::table('iku_kinerja_layanan', function (Blueprint $table) {
            $table->foreignId('kategori_id')
                ->after('id')
                ->constrained('kategori_kinerja_layanan')
                ->cascadeOnDelete();
        });
    }
};
