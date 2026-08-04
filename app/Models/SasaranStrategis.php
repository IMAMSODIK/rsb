<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SasaranStrategis extends Model
{
    use HasFactory;

    protected $table = 'sasaran_strategis';

    protected $fillable = [
        'kode_sasaran_strategis',
        'uraian',
    ];
}
