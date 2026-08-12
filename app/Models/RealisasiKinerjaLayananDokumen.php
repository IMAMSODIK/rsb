<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiKinerjaLayananDokumen extends Model
{
    use HasFactory;

    protected $table = 'realisasi_kinerja_layanan_dokumen';

    protected $fillable = ['realisasi_id', 'tahun', 'semester', 'nama_asli', 'path', 'mime_type', 'ukuran'];

    public function realisasi(): BelongsTo
    {
        return $this->belongsTo(RealisasiKinerjaLayanan::class, 'realisasi_id');
    }
}
