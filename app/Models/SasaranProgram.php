<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SasaranProgram extends Model
{
    use HasFactory;

    protected $table = 'sasaran_program';

    protected $fillable = [
        'kode_sasaran_program',
        'uraian',
    ];
}
