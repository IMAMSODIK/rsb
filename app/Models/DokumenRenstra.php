<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DokumenRenstra extends Model
{
    use HasFactory;

    protected $table = 'dokumen_renstras';

    protected $fillable = ['tahun', 'uraian', 'nama_asli', 'path', 'mime_type', 'ukuran'];

    protected $casts = [
        'tahun' => 'integer',
        'ukuran' => 'integer',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
