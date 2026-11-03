<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RbaRealisasi extends Model
{
    use HasFactory;

    protected $table = 'rba_realisasi';

    protected $fillable = [
        'target_id',
        'semester_1',
        'semester_2',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'semester_1' => 'decimal:2',
            'semester_2' => 'decimal:2',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(RbaTarget::class, 'target_id');
    }

    public function total(): float
    {
        return (float) ($this->semester_1 ?? 0) + (float) ($this->semester_2 ?? 0);
    }
}
