<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriKinerjaLayanan extends Model
{
    use HasFactory;

    protected $table = 'kategori_kinerja_layanan';

    protected $fillable = [
        'layanan_id',
        'kode_kategori',
        'uraian',
        'unit_kerja_id',
    ];

    /**
     * Relasi ke Jenis Kinerja Layanan.
     */
    public function layanan(): BelongsTo
    {
        return $this->belongsTo(JenisKinerjaLayanan::class, 'layanan_id');
    }

    /**
     * Relasi ke Unit Kerja.
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * Relasi ke IKU Kinerja Layanan.
     */
    public function ikuLayanans(): HasMany
    {
        return $this->hasMany(IkuKinerjaLayanan::class, 'kategori_id');
    }
}
