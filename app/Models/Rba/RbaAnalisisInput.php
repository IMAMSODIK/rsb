<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RbaAnalisisInput extends Model
{
    use HasFactory;

    protected $table = 'rba_analisis_input';

    protected $fillable = [
        'tahun_anggaran_id',
        'semester',
        'variabel_kode',
        'nilai',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
        ];
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(RbaTahunAnggaran::class, 'tahun_anggaran_id');
    }
}
