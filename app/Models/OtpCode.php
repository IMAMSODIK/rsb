<?php

namespace App\Models;

use Database\Factories\OtpCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    /** @use HasFactory<OtpCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Scope: OTP yang masih valid (belum dipakai dan belum expired).
     */
    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
                     ->where('expires_at', '>', now());
    }

    /**
     * Cek apakah OTP sudah kadaluarsa.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Tandai OTP sebagai sudah digunakan.
     */
    public function markAsUsed(): bool
    {
        $this->used_at = now();
        return $this->save();
    }
}
