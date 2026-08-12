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
        'sasaran_strategis_id',
        'unit_kerja_id',
        'kode_iku',
        'uraian',
    ];

    public function sasaranStrategis(): BelongsTo
    {
        return $this->belongsTo(SasaranStrategis::class, 'sasaran_strategis_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
}
