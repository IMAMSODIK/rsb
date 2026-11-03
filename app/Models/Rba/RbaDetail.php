<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RbaDetail extends Model
{
    use HasFactory;

    protected $table = 'rba_detail';

    protected $fillable = [
        'indikator_id',
        'kode_detail',
        'uraian',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(RbaIndikator::class, 'indikator_id');
    }

    public function target(): HasMany
    {
        return $this->hasMany(RbaTarget::class, 'detail_id');
    }
}
