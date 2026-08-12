<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function getUraianTanpaUnitAttribute(): string
    {
        $uraian = (string) $this->uraian;
        $namaUnit = $this->unitKerja?->nama_unit;
        $suffix = $namaUnit ? ' - '.$namaUnit : null;

        return $suffix && str_ends_with($uraian, $suffix)
            ? substr($uraian, 0, -strlen($suffix))
            : $uraian;
    }

}
