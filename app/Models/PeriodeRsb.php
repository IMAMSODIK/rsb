<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeRsb extends Model
{
    use HasFactory;

    protected $table = 'periode_rsb';

    protected $fillable = [
        'tahun_awal',
        'tahun_akhir',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
