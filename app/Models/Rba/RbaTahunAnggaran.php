<?php

namespace App\Models\Rba;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RbaTahunAnggaran extends Model
{
    use HasFactory;

    protected $table = 'rba_tahun_anggaran';

    public const STATUS_OPEN = 'OPEN';

    public const STATUS_LOCKED = 'LOCKED';

    public const STATUS_CLOSED = 'CLOSED';

    protected $fillable = [
        'tahun',
        'status',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
        ];
    }

    public function target(): HasMany
    {
        return $this->hasMany(RbaTarget::class, 'tahun_anggaran_id');
    }

    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_LOCKED, self::STATUS_CLOSED], true);
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
