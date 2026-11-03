<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RbaKategori extends Model
{
    use HasFactory;

    protected $table = 'rba_kategori';

    protected $fillable = [
        'jenis_id',
        'kode_kategori',
        'uraian',
    ];

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(RbaJenis::class, 'jenis_id');
    }

    public function indikator(): HasMany
    {
        return $this->hasMany(RbaIndikator::class, 'kategori_id');
    }
}
