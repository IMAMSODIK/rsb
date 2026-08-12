<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SasaranProgram extends Model
{
    use HasFactory;

    protected $table = 'sasaran_program';

    protected $fillable = [
        'tujuan_id',
        'kode_sasaran_program',
        'uraian',
    ];

    public function tujuan(): BelongsTo
    {
        return $this->belongsTo(TujuanRsb::class, 'tujuan_id');
    }

    public function sasaranStrategis(): HasMany
    {
        return $this->hasMany(SasaranStrategis::class, 'sasaran_program_id');
    }
}
