<?php

namespace App\Http\Controllers;

use App\Models\IkuKinerjaLayanan;
use App\Models\PeriodeRsb;
use App\Models\TargetKinerjaIkuRsb;
use App\Models\UnitKerja;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TargetKinerjaIkuRsbController extends Controller
{
    public function index()
    {
        $periode = PeriodeRsb::orderBy('tahun_awal')->get();
        $periodeAktif = $periode->firstWhere('is_active', true);
        $isAdmin = auth()->user()->role === 'admin';
        $unitKerjaList = $isAdmin ? UnitKerja::orderBy('kode_unit')->get() : collect();

        return view('monitoring.target-kinerja-iku-rsb', compact('periode', 'periodeAktif', 'isAdmin', 'unitKerjaList'));
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
            $iku = IkuKinerjaLayanan::when($unitKerja, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))
                ->with(['sasaranStrategis.sasaranProgram.tujuan', 'unitKerja'])
                ->whereHas('sasaranStrategis.sasaranProgram.tujuan', fn ($query) => $query->where(function ($q) use ($periode) {
                    $q->whereNull('periode_rsb_id')->orWhere('periode_rsb_id', $periode->id);
                }))
                ->orderBy('kode_iku')
                ->get();
            $targets = TargetKinerjaIkuRsb::where('periode_rsb_id', $periode->id)
                ->when($unitKerja, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))
                ->get()
                ->keyBy('iku_kinerja_layanan_id');

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
                'data' => $iku->map(function ($item) use ($targets) {
                    return [
                        'id' => $item->id,
                        'kode_iku' => $item->kode_iku,
                        'uraian' => $item->uraian_tanpa_unit,
                        'tujuan_rsb' => $item->sasaranStrategis?->sasaranProgram?->tujuan
                            ? $item->sasaranStrategis->sasaranProgram->tujuan->kode_tujuan.' - '.$item->sasaranStrategis->sasaranProgram->tujuan->uraian
                            : '-',
                        'sasaran_program' => $item->sasaranStrategis?->sasaranProgram
                            ? $item->sasaranStrategis->sasaranProgram->kode_sasaran_program.' - '.$item->sasaranStrategis->sasaranProgram->uraian
                            : '-',
                        'sasaran_strategis' => $item->sasaranStrategis
                            ? $item->sasaranStrategis->kode_sasaran_strategis.' - '.$item->sasaranStrategis->uraian
                            : '-',
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
            Log::error('TargetKinerjaIkuRsb data error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat target kinerja IKU RSB.',
            ], 500);
        }
    }

    public function saveDraft(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'periode_id' => 'required|exists:periode_rsb,id',
                'iku_id' => 'required|exists:iku_kinerja_layanan,id',
                'target_tahunan' => 'required|array',
            ]);
            $isAdmin = auth()->user()->role === 'admin';
            $unitKerja = $isAdmin ? null : $this->operatorUnit();
            if (!$isAdmin && !$unitKerja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit kerja operator belum ditentukan.',
                ], 422);
            }

            $periode = PeriodeRsb::findOrFail($data['periode_id']);
            $iku = IkuKinerjaLayanan::where('id', $data['iku_id'])
                ->when($unitKerja, fn ($query) => $query->where('unit_kerja_id', $unitKerja->id))
                ->first();
            if (!$iku) {
                return response()->json([
                    'success' => false,
                    'message' => 'IKU tidak sesuai dengan unit kerja operator.',
                ], 422);
            }
            $tujuanPeriode = $iku->sasaranStrategis?->sasaranProgram?->tujuan?->periode_rsb_id;
            if ($tujuanPeriode !== null && (int) $tujuanPeriode !== (int) $periode->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tujuan RSB dari IKU ini tidak terdaftar pada periode terpilih.',
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

            TargetKinerjaIkuRsb::updateOrCreate(
                [
                    'periode_rsb_id' => $periode->id,
                    'iku_kinerja_layanan_id' => $iku->id,
                    'unit_kerja_id' => $unitKerja?->id ?? $iku->unit_kerja_id,
                ],
                [
                    'target_tahunan' => $targets,
                    'status' => 'draft',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Draft target IKU berhasil disimpan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('TargetKinerjaIkuRsb draft error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Draft target IKU gagal disimpan.',
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
