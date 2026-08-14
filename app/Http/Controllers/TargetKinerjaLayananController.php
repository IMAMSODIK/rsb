<?php

namespace App\Http\Controllers;

use App\Models\KategoriKinerjaLayanan;
use App\Models\PeriodeRsb;
use App\Models\TargetKinerjaLayanan;
use App\Models\UnitKerja;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TargetKinerjaLayananController extends Controller
{
    public function index()
    {
        $periode = PeriodeRsb::orderBy('tahun_awal')->get();
        $periodeAktif = $periode->firstWhere('is_active', true);
        $isAdmin = auth()->user()->role === 'admin';
        $unitKerjaList = $isAdmin ? UnitKerja::orderBy('kode_unit')->get() : collect();

        return view('monitoring.target-kinerja-layanan', compact('periode', 'periodeAktif', 'isAdmin', 'unitKerjaList'));
    }

    public function data(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'periode_id' => 'required|exists:periode_rsb,id',
                'unit_kerja_id' => 'nullable|exists:unit_kerja,id',
            ]);
            $isAdmin = auth()->user()->role === 'admin';
            $unitKerja = $isAdmin
                ? (empty($data['unit_kerja_id']) ? null : UnitKerja::find($data['unit_kerja_id']))
                : $this->operatorUnit();
            if (!$isAdmin && !$unitKerja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit kerja operator belum ditentukan.',
                ], 422);
            }

            $periode = PeriodeRsb::findOrFail($data['periode_id']);
            $years = range((int) $periode->tahun_awal, (int) $periode->tahun_akhir);
            $kategori = KategoriKinerjaLayanan::with(['layanan', 'unitKerja'])
                ->when($unitKerja, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))
                ->get()
                ->sortBy(fn ($item) => sprintf(
                    '%s|%s|%s|%s',
                    $item->unitKerja?->kode_unit ?? '',
                    $item->layanan?->kode_layanan ?? '',
                    $item->kode_kategori ?? '',
                    $item->uraian ?? ''
                ));
            $targets = TargetKinerjaLayanan::where('periode_rsb_id', $periode->id)
                ->when($unitKerja, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))
                ->get()
                ->keyBy('kategori_id');

            return response()->json([
                'success' => true,
                'unit_kerja' => [
                    'kode_unit' => $unitKerja?->kode_unit ?? 'ALL',
                    'nama_unit' => $unitKerja?->nama_unit ?? 'Semua Unit Kerja',
                ],
                'periode' => [
                    'id' => $periode->id,
                    'tahun_awal' => $periode->tahun_awal,
                    'tahun_akhir' => $periode->tahun_akhir,
                ],
                'years' => $years,
                'data' => $kategori->map(function ($item) use ($targets) {
                    return [
                        'id' => $item->id,
                        'kode_kategori' => $item->kode_kategori,
                        'uraian' => $item->uraian_tanpa_unit,
                        'unit_kerja' => $item->unitKerja ? $item->unitKerja->kode_unit.' - '.$item->unitKerja->nama_unit : '-',
                        'jenis_kinerja_layanan' => $item->layanan ? $item->layanan->kode_layanan.' - '.$item->layanan->uraian : '-',
                        'target_tahunan' => $targets->get($item->id)?->target_tahunan ?? [],
                        'status' => $targets->get($item->id)?->status ?? 'draft',
                    ];
                })->values(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('TargetKinerjaLayanan data error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat target kinerja layanan.',
            ], 500);
        }
    }

    public function saveDraft(Request $request): JsonResponse
    {
        try {
            if (auth()->user()->role === 'admin') {
                return response()->json(['success' => false, 'message' => 'Admin hanya dapat melihat data monitoring.'], 403);
            }
            $data = $request->validate([
                'periode_id' => 'required|exists:periode_rsb,id',
                'kategori_id' => 'required|exists:kategori_kinerja_layanan,id',
                'target_tahunan' => 'required|array',
            ]);
            $unitKerja = $this->operatorUnit();
            if (!$unitKerja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit kerja operator belum ditentukan.',
                ], 422);
            }

            $periode = PeriodeRsb::findOrFail($data['periode_id']);
            $kategori = KategoriKinerjaLayanan::where('id', $data['kategori_id'])
                ->where('unit_kerja_id', $unitKerja->id)
                ->first();
            if (!$kategori) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak sesuai dengan unit kerja operator.',
                ], 422);
            }

            $years = range((int) $periode->tahun_awal, (int) $periode->tahun_akhir);
            $targets = [];
            foreach ($data['target_tahunan'] as $year => $value) {
                if (!in_array((int) $year, $years, true)) {
                    continue;
                }
                $targets[(string) $year] = $this->performanceValue($value);
            }

            TargetKinerjaLayanan::updateOrCreate(
                [
                    'periode_rsb_id' => $periode->id,
                    'kategori_id' => $kategori->id,
                    'unit_kerja_id' => $unitKerja->id,
                ],
                [
                    'target_tahunan' => $targets,
                    'status' => 'draft',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Draft target berhasil disimpan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('TargetKinerjaLayanan draft error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Draft target gagal disimpan.',
            ], 500);
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
                'target_tahunan' => 'Target tahunan harus berupa angka atau teks.',
            ]);
        }
        if (is_numeric($value)) {
            if ($value < 0) {
                throw ValidationException::withMessages([
                    'target_tahunan' => 'Target numerik tidak boleh kurang dari nol.',
                ]);
            }

            return (float) $value;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
