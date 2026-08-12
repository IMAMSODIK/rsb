<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetKinerjaIkuRsb extends Model
{
    use HasFactory;

    protected $table = 'target_kinerja_iku_rsb';

    protected $fillable = [
        'periode_rsb_id',
        'iku_kinerja_layanan_id',
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

    public function iku(): BelongsTo
    {
        return $this->belongsTo(IkuKinerjaLayanan::class, 'iku_kinerja_layanan_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
}
