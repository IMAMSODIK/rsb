<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaDetail;
use App\Models\Rba\RbaRealisasi;
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

class RbaRealisasiController extends Controller
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

            return view('rba.realisasi.index', compact('tahun', 'current'));
        } catch (Exception $e) {
            Log::error('RbaRealisasi index error: '.$e->getMessage());

            // Jangan redirect()->back(): jika dibuka dari dashboard, view
            // yang gagal render akan terlihat seperti "mental ke dashboard"
            // dan menyembunyikan penyebab aslinya. Gagalkan eksplisit.
            abort(500, 'Gagal memuat halaman Realisasi RBA: '.$e->getMessage());
        }
    }

    public function data(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'tahun_anggaran_id' => 'required|exists:rba_tahun_anggaran,id',
            ]);

            $tahun = RbaTahunAnggaran::findOrFail($input['tahun_anggaran_id']);

            // Window perbandingan: tahun dipilih -3 s/d +1.
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
            $targets = RbaTarget::with(['detail.indikator.kategori.jenis', 'realisasi'])
                ->whereIn('tahun_anggaran_id', array_keys($tahunIdToYear))
                ->get();

            // Kelompokkan per detail: [detail_id] => baris per tahun.
            $grouped = [];
            foreach ($targets as $target) {
                $y = $tahunIdToYear[$target->tahun_anggaran_id] ?? null;
                if ($y === null || ! $target->detail) {
                    continue;
                }
                $nilai = (float) $target->nilai;
                $semester1 = $target->realisasi?->semester_1 === null ? null : (float) $target->realisasi->semester_1;
                $semester2 = $target->realisasi?->semester_2 === null ? null : (float) $target->realisasi->semester_2;
                $total = ($semester1 ?? 0) + ($semester2 ?? 0);
                if (! isset($grouped[$target->detail_id])) {
                    $grouped[$target->detail_id] = [
                        'id' => $target->detail->id,
                        'kode_detail' => $target->detail->kode_detail,
                        'uraian' => $target->detail->uraian,
                        'kode_indikator' => $target->detail->indikator?->kode_indikator,
                        'indikator' => $target->detail->indikator?->uraian,
                        'kode_kategori' => $target->detail->indikator?->kategori?->kode_kategori,
                        'kategori' => $target->detail->indikator?->kategori?->uraian,
                        'kode_jenis' => $target->detail->indikator?->kategori?->jenis?->kode_jenis,
                        'jenis' => $target->detail->indikator?->kategori?->jenis?->uraian,
                        'per_tahun' => [],
                    ];
                }
                $grouped[$target->detail_id]['per_tahun'][$y] = [
                    'target_id' => $target->id,
                    'target' => $nilai,
                    'semester_1' => $semester1,
                    'semester_2' => $semester2,
                    'total' => $total,
                    'persentase' => $nilai > 0 && $target->realisasi ? round(($total / $nilai) * 100, 2) : null,
                    'keterangan' => $target->realisasi?->keterangan,
                ];
            }

            // Detail tanpa target di semua tahun window tetap tampil (baris kosong).
            $allDetails = RbaDetail::with('indikator.kategori.jenis')->orderBy('kode_detail')->get();
            $data = $allDetails->map(function ($detail) use ($grouped) {
                $row = [
                    'id' => $detail->id,
                    'kode_detail' => $detail->kode_detail,
                    'uraian' => $detail->uraian,
                    'kode_indikator' => $detail->indikator?->kode_indikator,
                    'indikator' => $detail->indikator?->uraian,
                    'kode_kategori' => $detail->indikator?->kategori?->kode_kategori,
                    'kategori' => $detail->indikator?->kategori?->uraian,
                    'kode_jenis' => $detail->indikator?->kategori?->jenis?->kode_jenis,
                    'jenis' => $detail->indikator?->kategori?->jenis?->uraian,
                    'per_tahun' => [],
                ];

                return array_merge($row, $grouped[$detail->id] ?? []);
            })->values();

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
                'data' => $data,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaRealisasi data error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat realisasi RBA.',
            ], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'tahun_anggaran_id' => 'required|exists:rba_tahun_anggaran,id',
                'rows' => 'required|array|min:1',
                'rows.*.target_id' => 'required|exists:rba_target,id',
                'rows.*.semester_1' => 'nullable|numeric|min:0|max:999999999999999.99',
                'rows.*.semester_2' => 'nullable|numeric|min:0|max:999999999999999.99',
                'rows.*.keterangan' => 'nullable|string|max:5000',
                'alasan' => 'nullable|string|max:1000',
            ], [
                'rows.required' => 'Data realisasi wajib diisi.',
                'rows.*.semester_1.numeric' => 'Realisasi semester 1 harus berupa angka Rupiah.',
                'rows.*.semester_2.numeric' => 'Realisasi semester 2 harus berupa angka Rupiah.',
            ]);

            $tahun = RbaTahunAnggaran::findOrFail($input['tahun_anggaran_id']);
            $alasan = $this->guard->ensureWritable($tahun, $input['alasan'] ?? null);
            $isOverride = $this->guard->isOverride($tahun);

            $saved = 0;
            foreach ($input['rows'] as $row) {
                $target = RbaTarget::where('id', $row['target_id'])
                    ->where('tahun_anggaran_id', $tahun->id)
                    ->first();
                if (! $target) {
                    continue;
                }
                $semester1 = $row['semester_1'] === null || $row['semester_1'] === '' ? null : (float) $row['semester_1'];
                $semester2 = $row['semester_2'] === null || $row['semester_2'] === '' ? null : (float) $row['semester_2'];
                $keterangan = trim((string) ($row['keterangan'] ?? '')) === '' ? null : trim((string) $row['keterangan']);

                $existing = $target->realisasi;
                if ($existing) {
                    $sebelum = $existing->getAttributes();
                    $existing->update([
                        'semester_1' => $semester1,
                        'semester_2' => $semester2,
                        'keterangan' => $keterangan,
                    ]);
                    $this->history->record(
                        $isOverride ? 'OVERRIDE' : 'UPDATE',
                        'rba_realisasi',
                        $existing->id,
                        $sebelum,
                        $existing->fresh(),
                        $alasan
                    );
                } else {
                    $created = RbaRealisasi::create([
                        'target_id' => $target->id,
                        'semester_1' => $semester1,
                        'semester_2' => $semester2,
                        'keterangan' => $keterangan,
                    ]);
                    $this->history->record(
                        $isOverride ? 'OVERRIDE' : 'INSERT',
                        'rba_realisasi',
                        $created->id,
                        null,
                        $created->fresh(),
                        $alasan
                    );
                }
                $saved++;
            }

            return response()->json([
                'success' => true,
                'message' => $saved > 0 ? 'Realisasi RBA berhasil disimpan.' : 'Tidak ada realisasi yang disimpan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaRealisasi save error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Realisasi RBA gagal disimpan.',
            ], 500);
        }
    }

    public function detailOptions(Request $request): JsonResponse
    {
        $tahunId = $request->query('tahun_anggaran_id');
        if (empty($tahunId)) {
            return response()->json(['success' => true, 'data' => []]);
        }
        $usedDetailIds = RbaTarget::where('tahun_anggaran_id', $tahunId)->pluck('detail_id');
        $data = RbaDetail::with('indikator.kategori.jenis')
            ->whereNotIn('id', $usedDetailIds)
            ->orderBy('kode_detail')
            ->get()
            ->map(fn ($detail) => [
                'id' => $detail->id,
                'label' => $detail->kode_detail.' - '.$detail->uraian,
            ]);

        return response()->json(['success' => true, 'data' => $data]);
    }
}
