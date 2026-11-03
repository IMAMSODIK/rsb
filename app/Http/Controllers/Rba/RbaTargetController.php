<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaDetail;
use App\Models\Rba\RbaTahunAnggaran;
use App\Models\Rba\RbaTarget;
use App\Services\Rba\RbaHistoryLogger;
use App\Services\Rba\RbaModuleGuard;
use App\Services\Rba\RbaYearGuard;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RbaTargetController extends Controller
{
    public function __construct(
        private RbaHistoryLogger $history,
        private RbaYearGuard $guard
    ) {}

    public function index()
    {
        RbaModuleGuard::ensureInstalled();
        try {
            $tahun = RbaTahunAnggaran::orderBy('tahun')->get();
            $current = $tahun->firstWhere('is_current', true) ?? $tahun->sortByDesc('tahun')->first();

            return view('rba.target.index', compact('tahun', 'current'));
        } catch (Exception $e) {
            Log::error('RbaTarget index error: '.$e->getMessage());

            // Jangan redirect()->back(): jika dibuka dari dashboard, view
            // yang gagal render akan terlihat seperti "mental ke dashboard"
            // dan menyembunyikan penyebab aslinya. Gagalkan eksplisit.
            abort(500, 'Gagal memuat halaman Target RBA: '.$e->getMessage());
        }
    }

    public function data(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'tahun_anggaran_id' => 'required|exists:rba_tahun_anggaran,id',
            ]);

            $tahun = RbaTahunAnggaran::findOrFail($input['tahun_anggaran_id']);

            // Window perbandingan: tahun dipilih -3 s/d +1 (5 kolom).
            // Hanya baca data; tahun yang belum ada barisnya tampil kosong.
            $baseYear = (int) $tahun->tahun;
            $window = range($baseYear - 3, $baseYear + 1);
            $tahunRows = RbaTahunAnggaran::whereIn('tahun', $window)
                ->orderBy('tahun')
                ->get()
                ->keyBy('tahun');
            $tahunIdToYear = [];
            foreach ($tahunRows as $y => $row) {
                $tahunIdToYear[$row->id] = (int) $y;
            }
            $details = RbaDetail::with('indikator.kategori.jenis')
                ->orderBy('kode_detail')
                ->get();
            $targets = RbaTarget::whereIn('tahun_anggaran_id', array_keys($tahunIdToYear))
                ->get();

            // Petakan [detail_id][tahun] => nilai.
            $nilaiMap = [];
            foreach ($targets as $target) {
                $y = $tahunIdToYear[$target->tahun_anggaran_id] ?? null;
                if ($y !== null) {
                    $nilaiMap[$target->detail_id][$y] = (float) $target->nilai;
                }
            }

            return response()->json([
                'success' => true,
                'tahun' => [
                    'id' => $tahun->id,
                    'tahun' => $tahun->tahun,
                    'status' => $tahun->status,
                    'locked' => $tahun->isLocked(),
                ],
                'years' => array_map(function ($y) use ($tahunRows, $baseYear) {
                    $row = $tahunRows->get($y);

                    return [
                        'tahun' => $y,
                        'id' => $row?->id,
                        'exists' => $row !== null,
                        'status' => $row?->status,
                        'locked' => $row ? $row->isLocked() : false,
                        'selected' => $y === $baseYear,
                    ];
                }, $window),
                'data' => $details->map(function ($detail) use ($nilaiMap) {
                    return [
                        'id' => $detail->id,
                        'kode_detail' => $detail->kode_detail,
                        'uraian' => $detail->uraian,
                        'kode_indikator' => $detail->indikator?->kode_indikator,
                        'indikator' => $detail->indikator?->uraian,
                        'kode_kategori' => $detail->indikator?->kategori?->kode_kategori,
                        'kategori' => $detail->indikator?->kategori?->uraian,
                        'kode_jenis' => $detail->indikator?->kategori?->jenis?->kode_jenis,
                        'jenis' => $detail->indikator?->kategori?->jenis?->uraian,
                        'per_tahun' => $nilaiMap[$detail->id] ?? new \stdClass,
                    ];
                })->values(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaTarget data error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat target RBA.',
            ], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'tahun_anggaran_id' => 'required|exists:rba_tahun_anggaran,id',
                'targets' => 'required|array|min:1',
                'targets.*.detail_id' => 'required|exists:rba_detail,id',
                'targets.*.nilai' => 'nullable|numeric|min:0|max:999999999999999.99',
                'next_tahun_anggaran_id' => 'nullable|exists:rba_tahun_anggaran,id',
                'next_targets' => 'nullable|array',
                'next_targets.*.detail_id' => 'required_with:next_targets|exists:rba_detail,id',
                'next_targets.*.nilai' => 'nullable|numeric|min:0|max:999999999999999.99',
                'alasan' => 'nullable|string|max:1000',
                'next_alasan' => 'nullable|string|max:1000',
            ], [
                'targets.required' => 'Data target wajib diisi.',
                'targets.*.detail_id.required' => 'Detail RBA wajib diisi.',
                'targets.*.nilai.numeric' => 'Nilai target harus berupa angka Rupiah.',
                'targets.*.nilai.min' => 'Nilai target tidak boleh kurang dari nol.',
            ]);

            $tahun = RbaTahunAnggaran::findOrFail($input['tahun_anggaran_id']);
            $alasan = $this->guard->ensureWritable($tahun, $input['alasan'] ?? null);
            $isOverride = $this->guard->isOverride($tahun);

            $saved = $this->simpanBatch($tahun, $input['targets'], $alasan, $isOverride);

            // Tahun +1 ikut tersimpan bila dikirim (guard per tahun masing-masing).
            $savedNext = 0;
            if (! empty($input['next_tahun_anggaran_id']) && ! empty($input['next_targets'])) {
                $nextTahun = RbaTahunAnggaran::findOrFail($input['next_tahun_anggaran_id']);
                $nextAlasan = $this->guard->ensureWritable($nextTahun, $input['next_alasan'] ?? $input['alasan'] ?? null);
                $savedNext = $this->simpanBatch($nextTahun, $input['next_targets'], $nextAlasan, $this->guard->isOverride($nextTahun));
            }

            $total = $saved + $savedNext;

            return response()->json([
                'success' => true,
                'message' => $total > 0 ? 'Target RBA berhasil disimpan.' : 'Tidak ada target yang disimpan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaTarget save error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Target RBA gagal disimpan.',
            ], 500);
        }
    }

    /**
     * Simpan sekumpulan target untuk satu tahun (dipakai tahun acuan dan +1).
     */
    private function simpanBatch(RbaTahunAnggaran $tahun, array $rows, ?string $alasan, bool $isOverride): int
    {
        $saved = 0;
        foreach ($rows as $row) {
            $detail = RbaDetail::find($row['detail_id'] ?? null);
            if (! $detail) {
                continue;
            }
            $nilai = ($row['nilai'] ?? null) === null || ($row['nilai'] ?? '') === '' ? null : (float) $row['nilai'];

            $existing = RbaTarget::where('detail_id', $detail->id)
                ->where('tahun_anggaran_id', $tahun->id)
                ->first();

            if ($nilai === null) {
                if ($existing) {
                    if ($existing->realisasi()->exists()) {
                        throw ValidationException::withMessages([
                            'targets' => 'Target '.$detail->kode_detail.' sudah memiliki realisasi dan tidak dapat dikosongkan.',
                        ]);
                    }
                    $sebelum = $existing->getAttributes();
                    $existing->delete();
                    $this->history->record(
                        $isOverride ? 'OVERRIDE' : 'DELETE',
                        'rba_target',
                        $existing->id,
                        $sebelum,
                        null,
                        $alasan
                    );
                }
                continue;
            }

            if ($existing) {
                $sebelum = $existing->getAttributes();
                $existing->update(['nilai' => $nilai]);
                $this->history->record(
                    $isOverride ? 'OVERRIDE' : 'UPDATE',
                    'rba_target',
                    $existing->id,
                    $sebelum,
                    $existing->fresh(),
                    $alasan
                );
            } else {
                $created = RbaTarget::create([
                    'detail_id' => $detail->id,
                    'tahun_anggaran_id' => $tahun->id,
                    'nilai' => $nilai,
                ]);
                $this->history->record(
                    $isOverride ? 'OVERRIDE' : 'INSERT',
                    'rba_target',
                    $created->id,
                    null,
                    $created->fresh(),
                    $alasan
                );
            }
            $saved++;
        }

        return $saved;
    }
}
