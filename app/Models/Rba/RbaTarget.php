<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RbaTarget extends Model
{
    use HasFactory;

    protected $table = 'rba_target';

    protected $fillable = [
        'detail_id',
        'tahun_anggaran_id',
        'nilai',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
        ];
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(RbaDetail::class, 'detail_id');
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(RbaTahunAnggaran::class, 'tahun_anggaran_id');
    }

    public function realisasi(): HasOne
    {
        return $this->hasOne(RbaRealisasi::class, 'target_id');
    }

    public function pencapaian(): ?float
    {
        $realisasi = $this->realisasi;
        $target = (float) $this->nilai;
        if (! $realisasi || $target <= 0) {
            return null;
        }
        $total = (float) ($realisasi->semester_1 ?? 0) + (float) ($realisasi->semester_2 ?? 0);

        return round(($total / $target) * 100, 2);
    }
}
