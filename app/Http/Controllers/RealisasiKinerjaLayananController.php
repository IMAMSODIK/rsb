<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKinerjaLayanan;
use App\Models\RealisasiKinerjaLayananDokumen;
use App\Models\TargetKinerjaLayanan;
use App\Models\UnitKerja;
use App\Models\PeriodeRsb;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RealisasiKinerjaLayananController extends Controller
{
    public function index()
    {
        $periode = PeriodeRsb::orderBy('tahun_awal')->get();
        $periodeAktif = $periode->firstWhere('is_active', true);
        $isAdmin = auth()->user()->role === 'admin';
        $unitKerjaList = $isAdmin ? UnitKerja::orderBy('kode_unit')->get() : collect();

        return view('monitoring.realisasi-kinerja-layanan', compact('periode', 'periodeAktif', 'isAdmin', 'unitKerjaList'));
    }

    public function data(Request $request): JsonResponse
    {
        try {
            $data = $request->validate(['periode_id' => 'required|exists:periode_rsb,id', 'unit_kerja_id' => 'nullable|exists:unit_kerja,id']);
            $isAdmin = auth()->user()->role === 'admin';
            $unitKerja = $isAdmin
                ? (empty($data['unit_kerja_id']) ? null : UnitKerja::find($data['unit_kerja_id']))
                : $this->operatorUnit();
            if (!$isAdmin && !$unitKerja) {
                return response()->json(['success' => false, 'message' => 'Unit kerja operator belum ditentukan.'], 422);
            }

            $periode = PeriodeRsb::findOrFail($data['periode_id']);
            $years = range((int) $periode->tahun_awal, (int) $periode->tahun_akhir);
            $targets = TargetKinerjaLayanan::with(['kategori.layanan', 'kategori.unitKerja', 'unitKerja'])
                ->where('periode_rsb_id', $periode->id)
                ->when($unitKerja, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))
                ->get()
                ->sortBy(fn ($target) => sprintf(
                    '%s|%s|%s|%s',
                    $target->unitKerja?->kode_unit ?? '',
                    $target->kategori?->layanan?->kode_layanan ?? '',
                    $target->kategori?->kode_kategori ?? '',
                    $target->kategori?->uraian ?? ''
                ));
            $realisasi = RealisasiKinerjaLayanan::with('dokumen')
                ->whereIn('target_kinerja_layanan_id', $targets->pluck('id'))
                ->get()
                ->keyBy('target_kinerja_layanan_id');

            return response()->json([
                'success' => true,
                'unit_kerja' => ['kode_unit' => $unitKerja?->kode_unit ?? 'ALL', 'nama_unit' => $unitKerja?->nama_unit ?? 'Semua Unit Kerja'],
                'periode' => ['id' => $periode->id, 'tahun_awal' => $periode->tahun_awal, 'tahun_akhir' => $periode->tahun_akhir],
                'years' => $years,
                'data' => $targets->map(function ($target) use ($realisasi) {
                    $item = $realisasi->get($target->id);

                    return [
                        'id' => $target->id,
                        'kode_kategori' => $target->kategori->kode_kategori,
                        'uraian' => $target->kategori->uraian_tanpa_unit,
                        'unit_kerja' => $target->unitKerja ? $target->unitKerja->kode_unit.' - '.$target->unitKerja->nama_unit : '-',
                        'jenis_kinerja_layanan' => $target->kategori->layanan ? $target->kategori->layanan->kode_layanan.' - '.$target->kategori->layanan->uraian : '-',
                        'target_tahunan' => $target->target_tahunan ?? [],
                        'realisasi_tahunan' => $item?->realisasi_tahunan ?? [],
                        'keterangan' => $item?->keterangan ?? '',
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
            Log::error('RealisasiKinerjaLayanan data error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal memuat realisasi kinerja layanan.'], 500);
        }
    }

    public function saveDraft(Request $request): JsonResponse
    {
        try {
            if (auth()->user()->role === 'admin') {
                return response()->json(['success' => false, 'message' => 'Admin hanya dapat melihat data monitoring.'], 403);
            }
            $data = $request->validate([
                'target_id' => 'required|exists:target_kinerja_layanan,id',
                'realisasi_tahunan' => 'required|array',
                'keterangan' => 'nullable|string|max:5000',
            ]);
            $unitKerja = $this->operatorUnit();
            if (!$unitKerja) {
                return response()->json(['success' => false, 'message' => 'Unit kerja operator belum ditentukan.'], 422);
            }

            $target = TargetKinerjaLayanan::where('id', $data['target_id'])
                ->where('unit_kerja_id', $unitKerja->id)
                ->first();
            if (!$target) {
                return response()->json(['success' => false, 'message' => 'Target tidak sesuai dengan unit kerja operator.'], 422);
            }

            $period = $target->periode;
            $targets = $target->target_tahunan ?? [];
            $years = range((int) $period->tahun_awal, (int) $period->tahun_akhir);
            $values = [];
            foreach ($years as $year) {
                $value = $data['realisasi_tahunan'][(string) $year] ?? $data['realisasi_tahunan'][$year] ?? [];
                $semester1Data = is_array($value['semester_1'] ?? null) ? $value['semester_1'] : ['nilai' => ($value['semester_1'] ?? null)];
                $semester2Data = is_array($value['semester_2'] ?? null) ? $value['semester_2'] : ['nilai' => ($value['semester_2'] ?? null)];
                $semester1 = $this->performanceValue($semester1Data['nilai'] ?? null);
                $semester2 = $this->performanceValue($semester2Data['nilai'] ?? null);
                $targetValue = $targets[(string) $year] ?? $targets[$year] ?? null;
                $percentage = $this->percentage($targetValue, $semester1, $semester2);

                $values[(string) $year] = [
                    'semester_1' => [
                        'nilai' => $semester1,
                        'keterangan' => $this->textValue($semester1Data['keterangan'] ?? null),
                    ],
                    'semester_2' => [
                        'nilai' => $semester2,
                        'keterangan' => $this->textValue($semester2Data['keterangan'] ?? null),
                    ],
                    'persentase' => $percentage,
                ];
            }

            RealisasiKinerjaLayanan::updateOrCreate(
                ['target_kinerja_layanan_id' => $target->id],
                ['realisasi_tahunan' => $values, 'keterangan' => $data['keterangan'] ?? null, 'status' => 'draft']
            );

            return response()->json(['success' => true, 'message' => 'Draft realisasi berhasil disimpan.', 'realisasi_tahunan' => $values]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->first()[0]], 422);
        } catch (Exception $e) {
            Log::error('RealisasiKinerjaLayanan draft error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Draft realisasi gagal disimpan.'], 500);
        }
    }

    public function uploadDocuments(Request $request): JsonResponse
    {
        try {
            if (auth()->user()->role === 'admin') {
                return response()->json(['success' => false, 'message' => 'Admin hanya dapat melihat data monitoring.'], 403);
            }
            $data = $request->validate([
                'target_id' => 'required|exists:target_kinerja_layanan,id',
                'tahun' => 'required|integer',
                'semester' => 'required|in:1,2',
                'dokumen' => 'required|array|min:1|max:10',
                'dokumen.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            ], [
                'dokumen.required' => 'Minimal satu dokumen harus dipilih.',
                'dokumen.max' => 'Maksimal 10 dokumen sekali upload.',
                'dokumen.*.mimes' => 'Dokumen harus berformat PDF, Word, Excel, JPG, atau PNG.',
                'dokumen.*.max' => 'Ukuran setiap dokumen maksimal 10 MB.',
            ]);
            $unitKerja = $this->operatorUnit();
            $target = TargetKinerjaLayanan::where('id', $data['target_id'])
                ->where('unit_kerja_id', $unitKerja?->id)
                ->first();
            if (!$target) {
                return response()->json(['success' => false, 'message' => 'Target tidak sesuai dengan unit kerja operator.'], 422);
            }
            if (!in_array((int) $data['tahun'], range((int) $target->periode->tahun_awal, (int) $target->periode->tahun_akhir), true)) {
                return response()->json(['success' => false, 'message' => 'Tahun dokumen tidak sesuai dengan periode target.'], 422);
            }

            $realisasi = RealisasiKinerjaLayanan::firstOrCreate(
                ['target_kinerja_layanan_id' => $target->id],
                ['realisasi_tahunan' => [], 'status' => 'draft']
            );
            foreach ($request->file('dokumen') as $file) {
                $path = $file->store('realisasi-kinerja-layanan', 'public');
                $realisasi->dokumen()->create([
                    'tahun' => $data['tahun'],
                    'semester' => $data['semester'],
                    'nama_asli' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'ukuran' => $file->getSize(),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Dokumen berhasil diunggah.']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->first()[0]], 422);
        } catch (Exception $e) {
            Log::error('RealisasiKinerjaLayanan upload error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Dokumen gagal diunggah.'], 500);
        }
    }

    public function deleteDocument($document): JsonResponse
    {
        try {
            if (auth()->user()->role === 'admin') {
                return response()->json(['success' => false, 'message' => 'Admin hanya dapat melihat data monitoring.'], 403);
            }
            $unitKerja = $this->operatorUnit();
            $item = RealisasiKinerjaLayananDokumen::with('realisasi.target')
                ->where('id', $document)
                ->first();
            if (!$item || $item->realisasi->target->unit_kerja_id !== $unitKerja?->id) {
                return response()->json(['success' => false, 'message' => 'Dokumen tidak valid.'], 422);
            }

            if (Storage::disk('public')->exists($item->path)) {
                Storage::disk('public')->delete($item->path);
            }
            $item->delete();

            return response()->json(['success' => true, 'message' => 'Dokumen berhasil dihapus.']);
        } catch (Exception $e) {
            Log::error('RealisasiKinerjaLayanan delete document error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Dokumen gagal dihapus.'], 500);
        }
    }

    private function operatorUnit(): ?UnitKerja
    {
        return UnitKerja::where('operator_id', auth()->id())->first();
    }

    private function performanceValue($value): float|string|null
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            throw ValidationException::withMessages([
                'realisasi_tahunan' => 'Realisasi semester harus berupa angka atau teks.',
            ]);
        }
        if (is_numeric($value)) {
            if ($value < 0) {
                throw ValidationException::withMessages([
                    'realisasi_tahunan' => 'Realisasi numerik tidak boleh kurang dari nol.',
                ]);
            }

            return (float) $value;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function percentage($target, $semester1, $semester2): ?float
    {
        if (!is_numeric($target) || ($semester1 !== null && !is_numeric($semester1)) || ($semester2 !== null && !is_numeric($semester2)) || (float) $target <= 0) {
            return null;
        }

        return round((((float) ($semester1 ?? 0) + (float) ($semester2 ?? 0)) / (float) $target) * 100, 2);
    }

    private function textValue($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        if (mb_strlen((string) $value) > 5000) {
            throw ValidationException::withMessages([
                'realisasi_tahunan' => 'Keterangan maksimal 5000 karakter.',
            ]);
        }

        return trim((string) $value);
    }
}
