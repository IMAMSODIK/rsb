<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iku_kinerja_layanan', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')
                ->after('sasaran_strategis_id')
                ->nullable()
                ->constrained('unit_kerja')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('iku_kinerja_layanan', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropColumn('unit_kerja_id');
        });
    }
};
