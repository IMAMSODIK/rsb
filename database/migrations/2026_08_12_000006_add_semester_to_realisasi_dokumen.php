<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('realisasi_kinerja_layanan_dokumen', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->nullable()->after('realisasi_id');
            $table->unsignedTinyInteger('semester')->nullable()->after('tahun');
            $table->index(['tahun', 'semester'], 'realisasi_dokumen_tahun_semester_index');
        });
    }

    public function down(): void
    {
        Schema::table('realisasi_kinerja_layanan_dokumen', function (Blueprint $table) {
            $table->dropIndex('realisasi_dokumen_tahun_semester_index');
            $table->dropColumn(['tahun', 'semester']);
        });
    }
};
