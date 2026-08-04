<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IkuKinerjaLayanan extends Model
{
    use HasFactory;

    protected $table = 'iku_kinerja_layanan';

    protected $fillable = [
        'kategori_id',
        'kode_iku',
        'uraian',
    ];

    /**
     * Relasi ke Kategori Kinerja Layanan.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriKinerjaLayanan::class, 'kategori_id');
    }
}
