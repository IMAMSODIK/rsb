<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RbaAnalisisVariableTarget extends Model
{
    use HasFactory;

    protected $table = 'rba_analisis_variable_target';

    protected $fillable = [
        'tahun_anggaran_id',
        'variabel_kode',
        'target',
    ];

    protected function casts(): array
    {
        return [
            'target' => 'decimal:2',
        ];
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(RbaTahunAnggaran::class, 'tahun_anggaran_id');
    }
}
