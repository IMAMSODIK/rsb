<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TujuanRsb extends Model
{
    use HasFactory;

    protected $table = 'tujuan_rsb';

    protected $fillable = [
        'kode_tujuan',
        'uraian',
    ];
}
