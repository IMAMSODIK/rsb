<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitKerja extends Model
{
    use HasFactory;

    protected $table = 'unit_kerja';

    protected $fillable = [
        'kode_unit',
        'nama_unit',
        'operator_id',
    ];

    /**
     * Operator penanggung jawab unit kerja.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Relasi ke Kategori Kinerja Layanan.
     */
    public function kategoriLayanans(): HasMany
    {
        return $this->hasMany(KategoriKinerjaLayanan::class);
    }

    public function ikuKinerjaLayanans(): HasMany
    {
        return $this->hasMany(IkuKinerjaLayanan::class, 'unit_kerja_id');
    }
}
