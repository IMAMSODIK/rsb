<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RbaJenis extends Model
{
    use HasFactory;

    protected $table = 'rba_jenis';

    protected $fillable = [
        'kode_jenis',
        'uraian',
    ];

    public function kategori(): HasMany
    {
        return $this->hasMany(RbaKategori::class, 'jenis_id');
    }
}
