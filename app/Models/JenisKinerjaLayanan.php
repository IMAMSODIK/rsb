<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisKinerjaLayanan extends Model
{
    use HasFactory;

    protected $table = 'jenis_kinerja_layanan';

    protected $fillable = [
        'kode_layanan',
        'uraian',
    ];

    /**
     * Relasi ke Kategori Kinerja Layanan.
     */
    public function kategoriLayanans(): HasMany
    {
        return $this->hasMany(KategoriKinerjaLayanan::class, 'layanan_id');
    }
}
