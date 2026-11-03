<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RbaHistory extends Model
{
    use HasFactory;

    protected $table = 'rba_history';

    public const AKSI_INSERT = 'INSERT';

    public const AKSI_UPDATE = 'UPDATE';

    public const AKSI_DELETE = 'DELETE';

    public const AKSI_OVERRIDE = 'OVERRIDE';

    protected $fillable = [
        'aksi',
        'tabel_referensi',
        'referensi_id',
        'data_sebelum',
        'data_sesudah',
        'alasan',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'data_sebelum' => 'array',
            'data_sesudah' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
