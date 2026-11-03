<?php

namespace App\Services\Rba;

use App\Models\Rba\RbaHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RbaHistoryLogger
{
    public function record(
        string $aksi,
        string $tabelReferensi,
        ?int $referensiId,
        array|Model|null $sebelum = null,
        array|Model|null $sesudah = null,
        ?string $alasan = null,
        ?int $userId = null
    ): RbaHistory {
        try {
            return RbaHistory::create([
                'aksi' => $aksi,
                'tabel_referensi' => $tabelReferensi,
                'referensi_id' => $referensiId,
                'data_sebelum' => $this->normalize($sebelum),
                'data_sesudah' => $this->normalize($sesudah),
                'alasan' => $alasan,
                'user_id' => $userId ?? Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('RBA history error: '.$e->getMessage());

            throw $e;
        }
    }

    private function normalize(array|Model|null $data): ?array
    {
        if ($data === null) {
            return null;
        }

        if ($data instanceof Model) {
            return $data->getAttributes();
        }

        return $data;
    }
}
