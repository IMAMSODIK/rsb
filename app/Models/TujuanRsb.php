<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TujuanRsb extends Model
{
    use HasFactory;

    protected $table = 'tujuan_rsb';

    protected $fillable = [
        'kode_tujuan',
        'uraian',
    ];

    public function sasaranPrograms(): HasMany
    {
        return $this->hasMany(SasaranProgram::class, 'tujuan_id');
    }
}
