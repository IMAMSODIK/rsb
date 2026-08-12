<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RealisasiKinerjaIkuRsb extends Model
{
    use HasFactory;

    protected $table = 'realisasi_kinerja_iku_rsb';

    protected $fillable = ['target_kinerja_iku_rsb_id', 'realisasi_tahunan', 'status'];

    protected function casts(): array
    {
        return ['realisasi_tahunan' => 'array'];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(TargetKinerjaIkuRsb::class, 'target_kinerja_iku_rsb_id');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(RealisasiKinerjaIkuRsbDokumen::class, 'realisasi_id');
    }
}
