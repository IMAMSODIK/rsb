<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_kinerja_layanan', function (Blueprint $table) {
            $table->foreignId('periode_rsb_id')
                ->after('uraian')
                ->nullable()
                ->constrained('periode_rsb')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jenis_kinerja_layanan', function (Blueprint $table) {
            $table->dropForeign(['periode_rsb_id']);
            $table->dropColumn('periode_rsb_id');
        });
    }
};
