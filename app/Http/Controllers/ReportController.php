<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKinerjaIkuRsb;
use App\Models\RealisasiKinerjaLayanan;
use App\Models\TargetKinerjaIkuRsb;
use App\Models\TargetKinerjaLayanan;
use App\Models\PeriodeRsb;
use App\Models\UnitKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function layanan()
    {
        return $this->reportView('layanan');
    }

    public function iku()
    {
        return $this->reportView('iku');
    }

    public function dataLayanan(Request $request): JsonResponse
    {
        return response()->json($this->buildReport($request, 'layanan'));
    }

    public function dataIku(Request $request): JsonResponse
    {
        return response()->json($this->buildReport($request, 'iku'));
    }

    public function exportLayanan(Request $request, string $format)
    {
        return $this->export($request, 'layanan', $format);
    }

    public function exportIku(Request $request, string $format)
    {
        return $this->export($request, 'iku', $format);
    }

    private function reportView(string $type)
    {
        $periode = PeriodeRsb::orderBy('tahun_awal')->get();
        $periodeAktif = $periode->firstWhere('is_active', true);

        return view('reports.index', [
            'type' => $type,
            'title' => $type === 'layanan' ? 'Laporan Capaian Kinerja Layanan' : 'Laporan Capaian Kinerja IKU',
            'periode' => $periode,
            'periodeAktif' => $periodeAktif,
        ]);
    }

    private function buildReport(Request $request, string $type): array
    {
        try {
            $input = $request->validate([
                'periode_id' => 'required|exists:periode_rsb,id',
                'mode' => 'required|in:tahunan,semester_1,semester_2',
            ]);
            $isAdmin = auth()->user()->role === 'admin';
            $unitKerja = $isAdmin ? null : UnitKerja::where('operator_id', auth()->id())->first();
            if (!$isAdmin && !$unitKerja) {
                return ['success' => false, 'message' => 'Unit kerja operator belum ditentukan.'];
            }
            $periode = PeriodeRsb::findOrFail($input['periode_id']);
            $years = range((int) $periode->tahun_awal, (int) $periode->tahun_akhir);
            $rows = $type === 'layanan'
                ? $this->serviceRows($periode, $unitKerja, $input['mode'], $years)
                : $this->ikuRows($periode, $unitKerja, $input['mode'], $years);

            return [
                'success' => true,
                'unit_kerja' => $unitKerja ? $unitKerja->kode_unit.' - '.$unitKerja->nama_unit : 'Semua Unit Kerja',
                'periode' => $periode->tahun_awal.' - '.$periode->tahun_akhir,
                'mode' => $input['mode'],
                'rows' => $rows,
            ];
        } catch (ValidationException $e) {
            return ['success' => false, 'message' => collect($e->errors())->first()[0]];
        } catch (\Throwable $e) {
            Log::error('Report data error: '.$e->getMessage());
            return ['success' => false, 'message' => 'Gagal memuat laporan.'];
        }
    }

    private function serviceRows(PeriodeRsb $periode, ?UnitKerja $unitKerja, string $mode, array $years): array
    {
        $targets = TargetKinerjaLayanan::with('kategori')
            ->where('periode_rsb_id', $periode->id)->when($unitKerja, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))->get();
        $realisasi = RealisasiKinerjaLayanan::with('dokumen')->whereIn('target_kinerja_layanan_id', $targets->pluck('id'))
            ->get()->keyBy('target_kinerja_layanan_id');

        return $targets->map(fn ($target) => $this->rowData(
            $target->kategori->kode_kategori,
            $target->kategori->uraian,
            $target->target_tahunan ?? [],
            $realisasi->get($target->id)?->realisasi_tahunan ?? [],
            $mode,
            $years,
            $realisasi->get($target->id)?->dokumen
        ))->values()->all();
    }

    private function ikuRows(PeriodeRsb $periode, ?UnitKerja $unitKerja, string $mode, array $years): array
    {
        $targets = TargetKinerjaIkuRsb::with('iku.sasaranStrategis')
            ->where('periode_rsb_id', $periode->id)->when($unitKerja, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))->get();
        $realisasi = RealisasiKinerjaIkuRsb::with('dokumen')->whereIn('target_kinerja_iku_rsb_id', $targets->pluck('id'))
            ->get()->keyBy('target_kinerja_iku_rsb_id');

        return $targets->map(function ($target) use ($realisasi, $mode, $years) {
            $row = $this->rowData(
                $target->iku->kode_iku,
                $target->iku->uraian,
                $target->target_tahunan ?? [],
                $realisasi->get($target->id)?->realisasi_tahunan ?? [],
                $mode,
                $years,
                $realisasi->get($target->id)?->dokumen
            );
            $row['sasaran_strategis'] = $target->iku->sasaranStrategis?->kode_sasaran_strategis ?? '-';
            return $row;
        })->values()->all();
    }

    private function rowData(string $kode, string $uraian, array $targets, array $realisasi, string $mode, array $years, $documents = null): array
    {
        $targetTotal = 0;
        $realizationTotal = 0;
        $details = [];
        $documentGroups = $documents?->groupBy(fn ($doc) => $doc->tahun.'_'.$doc->semester) ?? collect();
        foreach ($years as $year) {
            $target = $this->number($targets[(string) $year] ?? $targets[$year] ?? null);
            $value = $realisasi[(string) $year] ?? $realisasi[$year] ?? [];
            $semester1 = $this->number(is_array($value['semester_1'] ?? null) ? ($value['semester_1']['nilai'] ?? null) : ($value['semester_1'] ?? null));
            $semester2 = $this->number(is_array($value['semester_2'] ?? null) ? ($value['semester_2']['nilai'] ?? null) : ($value['semester_2'] ?? null));
            $targetTotal += $target ?? 0;
            $realizationTotal += $mode === 'semester_1' ? ($semester1 ?? 0) : ($mode === 'semester_2' ? ($semester2 ?? 0) : (($semester1 ?? 0) + ($semester2 ?? 0)));
            $selectedValue = $mode === 'semester_1' ? $semester1 : ($mode === 'semester_2' ? $semester2 : (($semester1 ?? 0) + ($semester2 ?? 0)));
            $details[(string) $year] = [
                'target' => $target,
                'semester_1' => ['nilai' => $semester1, 'keterangan' => is_array($value['semester_1'] ?? null) ? ($value['semester_1']['keterangan'] ?? null) : null],
                'semester_2' => ['nilai' => $semester2, 'keterangan' => is_array($value['semester_2'] ?? null) ? ($value['semester_2']['keterangan'] ?? null) : null],
                'persentase' => $target !== null && $target > 0 ? round(($selectedValue / $target) * 100, 2) : null,
                'dokumen' => $documentGroups->mapWithKeys(function ($items, $key) {
                    return [$key => $items->map(fn ($doc) => ['nama_asli' => $doc->nama_asli, 'url' => Storage::disk('public')->url($doc->path)])->values()->all()];
                })->all(),
            ];
        }
        $realization = $realizationTotal;

        return [
            'kode' => $kode,
            'uraian' => $uraian,
            'target' => round($targetTotal, 2),
            'realisasi' => round($realization, 2),
            'persentase' => $targetTotal > 0 ? round(($realization / $targetTotal) * 100, 2) : null,
            'detail' => $details,
        ];
    }

    private function export(Request $request, string $type, string $format)
    {
        $report = $this->buildReport($request, $type);
        if (!($report['success'] ?? false)) {
            return response($report['message'] ?? 'Gagal membuat laporan.', 422);
        }
        $title = $type === 'layanan' ? 'Laporan Capaian Kinerja Layanan' : 'Laporan Capaian Kinerja IKU';
        if ($format === 'pdf') {
            return response()->view('reports.print', compact('report', 'title'));
        }

        $filename = strtolower(str_replace(' ', '-', $title)).'-'.date('Ymd').'.csv';
        return response()->streamDownload(function () use ($report, $type) {
            $output = fopen('php://output', 'w');
            fprintf($output, "\xEF\xBB\xBF");
            $headers = [$type === 'iku' ? 'Kode IKU' : 'Kode Kategori', 'Sasaran Strategis', 'Uraian', 'Tahun', 'Target'];
            if ($report['mode'] !== 'semester_2') {
                array_push($headers, 'Realisasi Semester 1', 'Keterangan Semester 1', 'Dokumen Semester 1');
            }
            if ($report['mode'] !== 'semester_1') {
                array_push($headers, 'Realisasi Semester 2', 'Keterangan Semester 2', 'Dokumen Semester 2');
            }
            $headers[] = 'Pencapaian (%)';
            fputcsv($output, $headers);
            foreach ($report['rows'] as $row) {
                foreach ($row['detail'] as $year => $detail) {
                    $values = [$row['kode'], $row['sasaran_strategis'] ?? '-', $row['uraian'], $year, $detail['target'] ?? '-'];
                    if ($report['mode'] !== 'semester_2') {
                        array_push($values, $detail['semester_1']['nilai'] ?? '-', $detail['semester_1']['keterangan'] ?? '-', collect($detail['dokumen'][$year.'_1'] ?? [])->pluck('nama_asli')->implode(', '));
                    }
                    if ($report['mode'] !== 'semester_1') {
                        array_push($values, $detail['semester_2']['nilai'] ?? '-', $detail['semester_2']['keterangan'] ?? '-', collect($detail['dokumen'][$year.'_2'] ?? [])->pluck('nama_asli')->implode(', '));
                    }
                    $values[] = $detail['persentase'] ?? '-';
                    fputcsv($output, $values);
                }
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function number($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
