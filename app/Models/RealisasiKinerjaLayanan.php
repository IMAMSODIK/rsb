<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RealisasiKinerjaLayanan extends Model
{
    use HasFactory;

    protected $table = 'realisasi_kinerja_layanan';

    protected $fillable = [
        'target_kinerja_layanan_id',
        'realisasi_tahunan',
        'keterangan',
        'status',
    ];

    protected function casts(): array
    {
        return ['realisasi_tahunan' => 'array'];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(TargetKinerjaLayanan::class, 'target_kinerja_layanan_id');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(RealisasiKinerjaLayananDokumen::class, 'realisasi_id');
    }
}
