<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TargetKinerjaLayanan extends Model
{
    use HasFactory;

    protected $table = 'target_kinerja_layanan';

    protected $fillable = [
        'periode_rsb_id',
        'kategori_id',
        'unit_kerja_id',
        'target_tahunan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_tahunan' => 'array',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeRsb::class, 'periode_rsb_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriKinerjaLayanan::class, 'kategori_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function realisasi(): HasOne
    {
        return $this->hasOne(RealisasiKinerjaLayanan::class);
    }
}
