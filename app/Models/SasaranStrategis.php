<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SasaranStrategis extends Model
{
    use HasFactory;

    protected $table = 'sasaran_strategis';

    protected $fillable = [
        'sasaran_program_id',
        'kode_sasaran_strategis',
        'uraian',
    ];

    public function sasaranProgram(): BelongsTo
    {
        return $this->belongsTo(SasaranProgram::class, 'sasaran_program_id');
    }

    public function ikuLayanans(): HasMany
    {
        return $this->hasMany(IkuKinerjaLayanan::class, 'sasaran_strategis_id');
    }
}
