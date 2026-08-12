<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKinerjaIkuRsb;
use App\Models\RealisasiKinerjaIkuRsbDokumen;
use App\Models\TargetKinerjaIkuRsb;
use App\Models\UnitKerja;
use App\Models\PeriodeRsb;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RealisasiKinerjaIkuRsbController extends Controller
{
    public function index()
    {
        $periode = PeriodeRsb::orderBy('tahun_awal')->get();
        $periodeAktif = $periode->firstWhere('is_active', true);
        $isAdmin = auth()->user()->role === 'admin';

        return view('monitoring.realisasi-kinerja-iku-rsb', compact('periode', 'periodeAktif', 'isAdmin'));
    }

    public function data(Request $request): JsonResponse
    {
        try {
            $data = $request->validate(['periode_id' => 'required|exists:periode_rsb,id']);
            $isAdmin = auth()->user()->role === 'admin';
            $unitKerja = $isAdmin ? null : $this->operatorUnit();
            if (!$isAdmin && !$unitKerja) {
                return response()->json(['success' => false, 'message' => 'Unit kerja operator belum ditentukan.'], 422);
            }

            $periode = PeriodeRsb::findOrFail($data['periode_id']);
            $years = range((int) $periode->tahun_awal, (int) $periode->tahun_akhir);
            $targets = TargetKinerjaIkuRsb::with('iku.sasaranStrategis')
                ->where('periode_rsb_id', $periode->id)
                ->when(!$isAdmin, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))
                ->orderBy('iku_kinerja_layanan_id')
                ->get();
            $realisasi = RealisasiKinerjaIkuRsb::with('dokumen')
                ->whereIn('target_kinerja_iku_rsb_id', $targets->pluck('id'))
                ->get()
                ->keyBy('target_kinerja_iku_rsb_id');

            return response()->json([
                'success' => true,
                'unit_kerja' => ['kode_unit' => $unitKerja?->kode_unit ?? 'ALL', 'nama_unit' => $unitKerja?->nama_unit ?? 'Semua Unit Kerja'],
                'periode' => ['id' => $periode->id, 'tahun_awal' => $periode->tahun_awal, 'tahun_akhir' => $periode->tahun_akhir],
                'years' => $years,
                'data' => $targets->map(function ($target) use ($realisasi) {
                    $item = $realisasi->get($target->id);

                    return [
                        'id' => $target->id,
                        'kode_iku' => $target->iku->kode_iku,
                        'sasaran_strategis' => $target->iku->sasaranStrategis?->kode_sasaran_strategis,
                        'uraian' => $target->iku->uraian,
                        'target_tahunan' => $target->target_tahunan ?? [],
                        'realisasi_tahunan' => $item?->realisasi_tahunan ?? [],
                        'status' => $item?->status ?? 'draft',
                        'dokumen' => $item?->dokumen->groupBy(fn ($doc) => $doc->tahun.'_'.$doc->semester)
                            ->map(fn ($documents) => $documents->map(fn ($doc) => [
                                'id' => $doc->id,
                                'nama_asli' => $doc->nama_asli,
                                'url' => Storage::disk('public')->url($doc->path),
                            ])->values())
                            ->all() ?? [],
                    ];
                })->values(),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->first()[0]], 422);
        } catch (Exception $e) {
            Log::error('RealisasiKinerjaIkuRsb data error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal memuat realisasi kinerja IKU RSB.'], 500);
        }
    }

    public function saveDraft(Request $request): JsonResponse
    {
        try {
            if (auth()->user()->role === 'admin') {
                return response()->json(['success' => false, 'message' => 'Admin hanya dapat melihat data monitoring.'], 403);
            }
            $data = $request->validate([
                'target_id' => 'required|exists:target_kinerja_iku_rsb,id',
                'realisasi_tahunan' => 'required|array',
            ]);
            $unitKerja = $this->operatorUnit();
            $target = TargetKinerjaIkuRsb::where('id', $data['target_id'])
                ->where('unit_kerja_id', $unitKerja?->id)
                ->first();
            if (!$target) {
                return response()->json(['success' => false, 'message' => 'Target tidak sesuai dengan unit kerja operator.'], 422);
            }

            $values = $this->buildAnnualValues($target, $data['realisasi_tahunan']);
            RealisasiKinerjaIkuRsb::updateOrCreate(
                ['target_kinerja_iku_rsb_id' => $target->id],
                ['realisasi_tahunan' => $values, 'status' => 'draft']
            );

            return response()->json(['success' => true, 'message' => 'Draft realisasi IKU berhasil disimpan.', 'realisasi_tahunan' => $values]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->first()[0]], 422);
        } catch (Exception $e) {
            Log::error('RealisasiKinerjaIkuRsb draft error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Draft realisasi IKU gagal disimpan.'], 500);
        }
    }

    public function uploadDocuments(Request $request): JsonResponse
    {
        try {
            if (auth()->user()->role === 'admin') {
                return response()->json(['success' => false, 'message' => 'Admin hanya dapat melihat data monitoring.'], 403);
            }
            $data = $request->validate([
                'target_id' => 'required|exists:target_kinerja_iku_rsb,id',
                'tahun' => 'required|integer',
                'semester' => 'required|in:1,2',
                'dokumen' => 'required|array|min:1|max:10',
                'dokumen.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            ]);
            $unitKerja = $this->operatorUnit();
            $target = TargetKinerjaIkuRsb::where('id', $data['target_id'])
                ->where('unit_kerja_id', $unitKerja?->id)
                ->first();
            if (!$target) {
                return response()->json(['success' => false, 'message' => 'Target tidak sesuai dengan unit kerja operator.'], 422);
            }
            if (!in_array((int) $data['tahun'], range((int) $target->periode->tahun_awal, (int) $target->periode->tahun_akhir), true)) {
                return response()->json(['success' => false, 'message' => 'Tahun dokumen tidak sesuai dengan periode target.'], 422);
            }

            $realisasi = RealisasiKinerjaIkuRsb::firstOrCreate(
                ['target_kinerja_iku_rsb_id' => $target->id],
                ['realisasi_tahunan' => [], 'status' => 'draft']
            );
            foreach ($request->file('dokumen') as $file) {
                $path = $file->store('realisasi-kinerja-iku-rsb', 'public');
                $realisasi->dokumen()->create([
                    'tahun' => $data['tahun'],
                    'semester' => $data['semester'],
                    'nama_asli' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'ukuran' => $file->getSize(),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Dokumen realisasi IKU berhasil diunggah.']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->first()[0]], 422);
        } catch (Exception $e) {
            Log::error('RealisasiKinerjaIkuRsb upload error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Dokumen realisasi IKU gagal diunggah.'], 500);
        }
    }

    public function deleteDocument($document): JsonResponse
    {
        try {
            if (auth()->user()->role === 'admin') {
                return response()->json(['success' => false, 'message' => 'Admin hanya dapat melihat data monitoring.'], 403);
            }
            $unitKerja = $this->operatorUnit();
            $item = RealisasiKinerjaIkuRsbDokumen::with('realisasi.target')
                ->where('id', $document)
                ->first();
            if (!$item || $item->realisasi->target->unit_kerja_id !== $unitKerja?->id) {
                return response()->json(['success' => false, 'message' => 'Dokumen tidak valid.'], 422);
            }
            if (Storage::disk('public')->exists($item->path)) Storage::disk('public')->delete($item->path);
            $item->delete();

            return response()->json(['success' => true, 'message' => 'Dokumen realisasi IKU berhasil dihapus.']);
        } catch (Exception $e) {
            Log::error('RealisasiKinerjaIkuRsb delete document error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Dokumen realisasi IKU gagal dihapus.'], 500);
        }
    }

    private function buildAnnualValues(TargetKinerjaIkuRsb $target, array $input): array
    {
        $values = [];
        $years = range((int) $target->periode->tahun_awal, (int) $target->periode->tahun_akhir);
        foreach ($years as $year) {
            $yearData = $input[(string) $year] ?? $input[$year] ?? [];
            $s1 = $this->numericValue(is_array($yearData['semester_1'] ?? null) ? ($yearData['semester_1']['nilai'] ?? null) : ($yearData['semester_1'] ?? null));
            $s2 = $this->numericValue(is_array($yearData['semester_2'] ?? null) ? ($yearData['semester_2']['nilai'] ?? null) : ($yearData['semester_2'] ?? null));
            $targetValue = $this->numericValue($target->target_tahunan[(string) $year] ?? $target->target_tahunan[$year] ?? null);
            $values[(string) $year] = [
                'semester_1' => ['nilai' => $s1, 'keterangan' => $this->textValue($yearData['semester_1']['keterangan'] ?? null)],
                'semester_2' => ['nilai' => $s2, 'keterangan' => $this->textValue($yearData['semester_2']['keterangan'] ?? null)],
                'persentase' => $targetValue !== null && $targetValue > 0 ? round((($s1 ?? 0) + ($s2 ?? 0)) / $targetValue * 100, 2) : null,
            ];
        }
        return $values;
    }

    private function operatorUnit(): ?UnitKerja
    {
        return UnitKerja::where('operator_id', auth()->id())->first();
    }

    private function numericValue($value): ?float
    {
        if ($value === null || $value === '') return null;
        if (!is_numeric($value) || $value < 0) {
            throw ValidationException::withMessages(['realisasi_tahunan' => 'Realisasi semester harus berupa angka nol atau lebih.']);
        }
        return (float) $value;
    }

    private function textValue($value): ?string
    {
        if ($value === null || trim((string) $value) === '') return null;
        if (mb_strlen((string) $value) > 5000) {
            throw ValidationException::withMessages(['realisasi_tahunan' => 'Keterangan maksimal 5000 karakter.']);
        }
        return trim((string) $value);
    }
}
