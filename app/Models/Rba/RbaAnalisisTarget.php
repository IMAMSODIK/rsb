<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RbaAnalisisTarget extends Model
{
    use HasFactory;

    protected $table = 'rba_analisis_target';

    protected $fillable = [
        'tahun_anggaran_id',
        'analisis_kode',
        'target',
    ];

    protected function casts(): array
    {
        return [
            'target' => 'decimal:4',
        ];
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(RbaTahunAnggaran::class, 'tahun_anggaran_id');
    }
}
