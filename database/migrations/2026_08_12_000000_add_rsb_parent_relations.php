<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sasaran_program', function (Blueprint $table) {
            $table->foreignId('tujuan_id')
                ->after('id')
                ->nullable()
                ->constrained('tujuan_rsb')
                ->cascadeOnDelete();
        });

        Schema::table('sasaran_strategis', function (Blueprint $table) {
            $table->foreignId('sasaran_program_id')
                ->after('id')
                ->nullable()
                ->constrained('sasaran_program')
                ->cascadeOnDelete();
        });

        Schema::table('iku_kinerja_layanan', function (Blueprint $table) {
            $table->foreignId('sasaran_strategis_id')
                ->after('kategori_id')
                ->nullable()
                ->constrained('sasaran_strategis')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('iku_kinerja_layanan', function (Blueprint $table) {
            $table->dropForeign(['sasaran_strategis_id']);
            $table->dropColumn('sasaran_strategis_id');
        });

        Schema::table('sasaran_strategis', function (Blueprint $table) {
            $table->dropForeign(['sasaran_program_id']);
            $table->dropColumn('sasaran_program_id');
        });

        Schema::table('sasaran_program', function (Blueprint $table) {
            $table->dropForeign(['tujuan_id']);
            $table->dropColumn('tujuan_id');
        });
    }
};
