<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TujuanRsb extends Model
{
    use HasFactory;

    protected $table = 'tujuan_rsb';

    protected $fillable = [
        'kode_tujuan',
        'uraian',
        'periode_rsb_id',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeRsb::class, 'periode_rsb_id');
    }

    public function sasaranPrograms(): HasMany
    {
        return $this->hasMany(SasaranProgram::class, 'tujuan_id');
    }
}
