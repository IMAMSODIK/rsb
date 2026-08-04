<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SliderItem extends Model
{
    use HasFactory;

    protected $table = 'slider_items';

    protected $fillable = [
        'judul',
        'gambar',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'gambar_url',
    ];

    /**
     * URL gambar untuk dipakai di <img src>. Bisa berupa URL eksternal
     * (disimpan apa adanya) atau path di disk public (diawali dengan asset('storage/...')).
     */
    public function getGambarUrlAttribute(): string
    {
        $g = $this->gambar ?? '';

        if (preg_match('#^https?://#i', $g)) {
            return $g;
        }

        return asset('storage/'.ltrim($g, '/'));
    }

    /**
     * Apakah gambar disimpan sebagai URL eksternal (bukan file lokal).
     */
    public function isExternalUrl(): bool
    {
        return (bool) preg_match('#^https?://#i', $this->gambar ?? '');
    }
}
