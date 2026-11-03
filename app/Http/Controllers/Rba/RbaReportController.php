<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaHistory;
use App\Models\Rba\RbaTahunAnggaran;
use App\Models\Rba\RbaTarget;
use App\Services\Rba\RbaModuleGuard;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RbaReportController extends Controller
{
    public function index()
    {
        RbaModuleGuard::ensureInstalled();
        try {
            $tahun = RbaTahunAnggaran::orderBy('tahun')->get();
            $current = $tahun->firstWhere('is_current', true) ?? $tahun->sortByDesc('tahun')->first();

            return view('rba.laporan.index', compact('tahun', 'current'));
        } catch (Exception $e) {
            Log::error('RbaReport index error: '.$e->getMessage());

            // Jangan redirect()->back(): jika dibuka dari dashboard, view
            // yang gagal render akan terlihat seperti "mental ke dashboard"
            // dan menyembunyikan penyebab aslinya. Gagalkan eksplisit.
            abort(500, 'Gagal memuat halaman Laporan RBA: '.$e->getMessage());
        }
    }

    public function data(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'tahun_anggaran_id' => 'required|exists:rba_tahun_anggaran,id',
                'mode' => 'required|in:target,semester_1,semester_2,tahunan',
                'jenis_id' => 'nullable|exists:rba_jenis,id',
            ]);
            $tahun = RbaTahunAnggaran::findOrFail($input['tahun_anggaran_id']);

            $targets = RbaTarget::with(['detail.indikator.kategori.jenis', 'realisasi'])
                ->where('tahun_anggaran_id', $tahun->id)
                ->when($input['jenis_id'] ?? null, fn ($query, $jenisId) => $query->whereHas(
                    'detail.indikator.kategori',
                    fn ($sub) => $sub->where('jenis_id', $jenisId)
                ))
                ->get()
                ->sortBy(fn ($target) => sprintf(
                    '%s|%s|%s|%s',
                    $target->detail?->indikator?->kategori?->jenis?->kode_jenis ?? '',
                    $target->detail?->indikator?->kategori?->kode_kategori ?? '',
                    $target->detail?->indikator?->kode_indikator ?? '',
                    $target->detail?->kode_detail ?? ''
                ));

            $rows = $targets->map(function ($target) use ($input) {
                $nilai = (float) $target->nilai;
                $semester1 = $target->realisasi?->semester_1 === null ? null : (float) $target->realisasi->semester_1;
                $semester2 = $target->realisasi?->semester_2 === null ? null : (float) $target->realisasi->semester_2;
                $total = ($semester1 ?? 0) + ($semester2 ?? 0);
                $selected = match ($input['mode']) {
                    'semester_1' => $semester1 ?? 0,
                    'semester_2' => $semester2 ?? 0,
                    default => $total,
                };
                $percentage = ($input['mode'] === 'target' || $nilai <= 0) ? null : round(($selected / $nilai) * 100, 2);

                return [
                    'kode_jenis' => $target->detail?->indikator?->kategori?->jenis?->kode_jenis,
                    'jenis' => $target->detail?->indikator?->kategori?->jenis?->uraian,
                    'kode_kategori' => $target->detail?->indikator?->kategori?->kode_kategori,
                    'kategori' => $target->detail?->indikator?->kategori?->uraian,
                    'kode_indikator' => $target->detail?->indikator?->kode_indikator,
                    'indikator' => $target->detail?->indikator?->uraian,
                    'kode_detail' => $target->detail?->kode_detail,
                    'uraian' => $target->detail?->uraian,
                    'target' => $nilai,
                    'semester_1' => $semester1,
                    'semester_2' => $semester2,
                    'total' => $total,
                    'persentase' => $percentage,
                    'keterangan' => $target->realisasi?->keterangan,
                ];
            })->values();

            $aggregate = fn ($key) => round(
                $rows->sum(fn ($row) => $key === 'persentase' ? 0 : (float) ($row[$key] ?? 0)),
                2
            );
            $totalTarget = $aggregate('target');
            $totalRealisasi = match ($input['mode']) {
                'semester_1' => $aggregate('semester_1'),
                'semester_2' => $aggregate('semester_2'),
                default => $aggregate('total'),
            };
            $rekapJenis = $rows->groupBy('kode_jenis')->map(function ($items, $kode) {
                $target = round($items->sum(fn ($row) => (float) $row['target']), 2);
                $realisasi = round($items->sum(fn ($row) => (float) $row['total']), 2);

                return [
                    'kode_jenis' => $kode,
                    'jenis' => $items->first()['jenis'] ?? '-',
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'persentase' => $target > 0 ? round(($realisasi / $target) * 100, 2) : null,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'tahun' => ['id' => $tahun->id, 'tahun' => $tahun->tahun, 'status' => $tahun->status],
                'mode' => $input['mode'],
                'rows' => $rows,
                'rekap' => [
                    'target' => $totalTarget,
                    'realisasi' => $totalRealisasi,
                    'persentase' => $totalTarget > 0 && $input['mode'] !== 'target'
                        ? round(($totalRealisasi / $totalTarget) * 100, 2)
                        : null,
                ],
                'rekap_jenis' => $rekapJenis,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaReport data error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat laporan RBA.',
            ], 500);
        }
    }

    public function history(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'tabel_referensi' => 'nullable|string|max:100',
                'aksi' => 'nullable|in:INSERT,UPDATE,DELETE,OVERRIDE',
                'limit' => 'nullable|integer|min:1|max:200',
            ]);

            $data = RbaHistory::with('user:id,name,email')
                ->when($input['tabel_referensi'] ?? null, fn ($query, $tabel) => $query->where('tabel_referensi', $tabel))
                ->when($input['aksi'] ?? null, fn ($query, $aksi) => $query->where('aksi', $aksi))
                ->orderByDesc('id')
                ->limit((int) ($input['limit'] ?? 100))
                ->get();

            return response()->json(['success' => true, 'data' => $data]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaHistory data error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat history RBA.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $input = $request->validate([
            'tahun_anggaran_id' => 'required|exists:rba_tahun_anggaran,id',
            'mode' => 'required|in:target,semester_1,semester_2,tahunan',
            'jenis_id' => 'nullable|exists:rba_jenis,id',
        ]);
        $report = $this->data($request)->getData(true);
        if (! ($report['success'] ?? false)) {
            return response($report['message'] ?? 'Gagal membuat laporan.', 422);
        }

        $tahun = $report['tahun']['tahun'];
        $filename = 'laporan-rba-'.$tahun.'-'.$report['mode'].'-'.date('Ymd').'.csv';

        return response()->streamDownload(function () use ($report) {
            $output = fopen('php://output', 'w');
            fprintf($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Kode Jenis', 'Jenis', 'Kode Kategori', 'Kategori', 'Kode Indikator', 'Indikator', 'Kode Detail', 'Uraian (Rupiah)', 'Target', 'Realisasi Semester 1', 'Realisasi Semester 2', 'Total Realisasi', 'Pencapaian (%)', 'Keterangan']);
            foreach ($report['rows'] as $row) {
                fputcsv($output, [
                    $row['kode_jenis'] ?? '-', $row['jenis'] ?? '-', $row['kode_kategori'] ?? '-', $row['kategori'] ?? '-',
                    $row['kode_indikator'] ?? '-', $row['indikator'] ?? '-', $row['kode_detail'] ?? '-', $row['uraian'] ?? '-',
                    $row['target'] ?? '-', $row['semester_1'] ?? '-', $row['semester_2'] ?? '-', $row['total'] ?? '-',
                    $row['persentase'] ?? '-', $row['keterangan'] ?? '-',
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function historyView()
    {
        RbaModuleGuard::ensureInstalled();
        return view('rba.history.index');
    }
}
