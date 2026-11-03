<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RbaIndikator extends Model
{
    use HasFactory;

    protected $table = 'rba_indikator';

    protected $fillable = [
        'kategori_id',
        'kode_indikator',
        'uraian',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RbaKategori::class, 'kategori_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(RbaDetail::class, 'indikator_id');
    }
}
