<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slider_items', function (Blueprint $table) {
            $table->id();
            $table->string('judul')->nullable();
            $table->text('gambar');
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('slider_items')->insert([
            [
                'judul' => 'Kampus Modern',
                'gambar' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDouqth3arL9x8jKRYKWCFfNQWyU8oxgP2AyizRbYJBcjRHYn61EdiITof0QLbjs-7T6BSnu6NGRaQpz7ax-_-PE9VGEGiC1F5rWWHCrHp5D8Mq3N_fybOYL6ue7C0c44_tGl_tVFlmWJyA3oqzHmDQgS3gQLEF7jTLxesbNYRByz0IA5LZMbFacnrIf3mfi7IAH4mh5VbStc6ErlgpD65brxeubfw0Gow_PXT4wdo6T93LSn7Bkkyl',
                'urutan' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'judul' => 'Auditorium',
                'gambar' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDXp4nNKAAB4TeykNRJH1ZxcfXxk_67PwVVeyneyL6WTH6VlK7Og66THdEKGlHSRFqmoDNe0XFtRJeRFRZULjW8USTNNOvPNlaw5yS63aVUKHxrPvxGlzpAQOdRp1rxJEYpLs4cIWvPC6xlPDknraNAgjTt6bR7kb5-WqDji0PnDQE4zb5k0oSg2T47DFVqroU_GGfXWiEQoEmmyNbaN-YhqdFm7x0stPVMadjzraoNCSZ9ezca6SpC',
                'urutan' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'judul' => 'Mahasiswa',
                'gambar' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBLiO3lMKLJa8fXS-4clkOujT7JK7j7PiNxFSy7ncZzwpGJ4XQcIQMkcNVy35NwuSjqDZzbvr8F7Fm0BwBzDD4KDTjbpAXQjv9L8Awb8sogz5nFHsQDr6gcLOdUNcnIiA-NLM9amdq3leg3o47AH-UUue8je1H8nnSxvHvpgj3_V6jq2UHBOzubumBetQKjRfb3eOT3jufy_YIYPFo6EQ6dsqKsvFyZ8ShmCSj_mRNbkW6eXB9hxd94',
                'urutan' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('slider_items');
    }
};
