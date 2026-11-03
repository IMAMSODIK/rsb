<?php

namespace App\Services\Rba;

use App\Models\Rba\RbaTahunAnggaran;
use Illuminate\Validation\ValidationException;

class RbaYearGuard
{
    /**
     * Pastikan data tahun terkunci hanya bisa diubah admin dengan alasan.
     */
    public function ensureWritable(RbaTahunAnggaran $tahun, ?string $alasan = null): ?string
    {
        if (! $tahun->isLocked()) {
            return null;
        }

        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        if (! $isAdmin) {
            throw ValidationException::withMessages([
                'tahun' => 'Tahun '.$tahun->tahun.' berstatus '.$tahun->status.', perubahan hanya oleh admin.',
            ]);
        }

        if (trim((string) $alasan) === '') {
            throw ValidationException::withMessages([
                'alasan' => 'Alasan override wajib diisi untuk data '.$tahun->status.'.',
            ]);
        }

        return trim((string) $alasan);
    }

    public function isOverride(RbaTahunAnggaran $tahun): bool
    {
        return $tahun->isLocked() && auth()->check() && auth()->user()->role === 'admin';
    }
}
