<?php

namespace App\Http\Controllers\Rba;

use App\Http\Controllers\Controller;
use App\Models\Rba\RbaAnalisisInput;
use App\Models\Rba\RbaAnalisisTarget;
use App\Models\Rba\RbaAnalisisVariableTarget;
use App\Models\Rba\RbaTahunAnggaran;
use App\Services\Rba\RbaAnalisisCalculator;
use App\Services\Rba\RbaAnalisisRegistry;
use App\Services\Rba\RbaHistoryLogger;
use App\Services\Rba\RbaModuleGuard;
use App\Services\Rba\RbaYearGuard;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RbaAnalisisController extends Controller
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

            return view('rba.analisis.index', [
                'tahun' => $tahun,
                'current' => $current,
                'variables' => RbaAnalisisRegistry::variables(),
                'definitions' => RbaAnalisisRegistry::definitions(),
                'jenisList' => RbaAnalisisRegistry::jenisList(),
            ]);
        } catch (Exception $e) {
            Log::error('RbaAnalisis index error: '.$e->getMessage());

            abort(500, 'Gagal memuat halaman Analisis RBA: '.$e->getMessage());
        }
    }

    public function data(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'tahun_anggaran_id' => 'required|exists:rba_tahun_anggaran,id',
                'jenis' => 'nullable|string|max:50',
            ]);

            $tahun = RbaTahunAnggaran::findOrFail($input['tahun_anggaran_id']);

            $definitions = RbaAnalisisRegistry::definitions();
            if (! empty($input['jenis'])) {
                $definitions = array_values(array_filter(
                    $definitions,
                    fn ($def) => $def['jenis'] === $input['jenis']
                ));
            }

            $inputs = RbaAnalisisInput::where('tahun_anggaran_id', $tahun->id)->get();
            $targets = RbaAnalisisTarget::where('tahun_anggaran_id', $tahun->id)->get()->keyBy('analisis_kode');

            $nilaiMap = [];
            foreach ($inputs as $row) {
                $nilaiMap[$row->variabel_kode][(int) $row->semester] = $row->nilai === null ? null : (float) $row->nilai;
            }

            // Peta nilai TAHUN LALU (semester 0) untuk operasi antar-tahun
            // (PJOL, PKL, TOL). Null-safe: tahun lalu boleh belum ada.
            $tahunLalu = RbaTahunAnggaran::where('tahun', (int) $tahun->tahun - 1)->first();
            $nilaiMapLalu = [];
            if ($tahunLalu) {
                foreach (RbaAnalisisInput::where('tahun_anggaran_id', $tahunLalu->id)->where('semester', 0)->get() as $row) {
                    $nilaiMapLalu[$row->variabel_kode] = $row->nilai === null ? null : (float) $row->nilai;
                }
            }

            $hasil = $this->hitungRows($definitions, $nilaiMap, $targets, $nilaiMapLalu, $tahunLalu);
            $variabelRows = $this->hitungVariabel(
                RbaAnalisisRegistry::variables(),
                $nilaiMap,
                RbaAnalisisVariableTarget::where('tahun_anggaran_id', $tahun->id)->get()->keyBy('variabel_kode')
            );

            return response()->json([
                'success' => true,
                'tahun' => [
                    'id' => $tahun->id,
                    'tahun' => $tahun->tahun,
                    'status' => $tahun->status,
                    'locked' => $tahun->isLocked(),
                ],
                'variables' => RbaAnalisisRegistry::variables(),
                'variable_values' => $nilaiMap,
                'variable_rows' => $variabelRows,
                'rows' => $hasil,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaAnalisis data error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat analisis RBA.',
            ], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        try {
            $input = $request->validate([
                'tahun_anggaran_id' => 'required|exists:rba_tahun_anggaran,id',
                'alasan' => 'nullable|string|max:500',
                'inputs' => 'present|array',
                'inputs.*.variabel_kode' => 'required|string|max:50',
                'inputs.*.semester' => 'required|integer|in:0,1,2',
                'inputs.*.nilai' => 'nullable|numeric|min:0|max:999999999999',
                'variable_targets' => 'present|array',
                'variable_targets.*.variabel_kode' => 'required|string|max:50',
                'variable_targets.*.target' => 'nullable|numeric|min:0|max:999999999999',
                'targets' => 'present|array',
                'targets.*.analisis_kode' => 'required|string|max:50',
                'targets.*.target' => 'nullable|numeric|min:0|max:999999999999',
            ]);

            $tahun = RbaTahunAnggaran::findOrFail($input['tahun_anggaran_id']);
            $alasan = $this->guard->ensureWritable($tahun, $input['alasan'] ?? null);
            $isOverride = $this->guard->isOverride($tahun);

            $allowedVars = array_column(RbaAnalisisRegistry::variables(), 'kode');
            $allowedAnalisis = array_column(RbaAnalisisRegistry::definitions(), 'kode');

            $savedInput = $this->simpanInput($tahun->id, $input['inputs'], $allowedVars, $alasan, $isOverride);
            $savedVarTarget = $this->simpanVariableTarget($tahun->id, $input['variable_targets'], $allowedVars, $alasan, $isOverride);
            $savedTarget = $this->simpanTarget($tahun->id, $input['targets'], $allowedAnalisis, $alasan, $isOverride);
            $total = $savedInput + $savedVarTarget + $savedTarget;

            // Hitung ulang hasil setelah simpan agar autosave bisa refresh
            // kolom Realisasi/Prognosa/Capaian tanpa reload tabel (kursor aman).
            $hitung = $this->hitung($tahun);

            return response()->json([
                'success' => true,
                'message' => $total > 0 ? 'Analisis RBA berhasil disimpan.' : 'Tidak ada data analisis yang disimpan.',
                'rows' => $hitung['rows'],
                'variable_rows' => $hitung['variable_rows'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('RbaAnalisis save error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Analisis RBA gagal disimpan.',
            ], 500);
        }
    }

    /**
     * Realisasi TAHUNAN variabel: semester 0 jika diisi, fallback S1+S2.
     * Prognosa variabel mengikuti aturan calculator (S2 ada => S1+S2, dst).
     *
     * @param  array<string, array<int, float|null>>  $nilaiMap  [kode][semester]
     */
    private function tahunanVariabel(array $nilaiMap, string $kode): ?float
    {
        if (array_key_exists(0, $nilaiMap[$kode] ?? []) && ($nilaiMap[$kode][0] ?? null) !== null) {
            return (float) $nilaiMap[$kode][0];
        }
        $s1 = $nilaiMap[$kode][1] ?? null;
        $s2 = $nilaiMap[$kode][2] ?? null;
        if ($s1 === null && $s2 === null) {
            return null;
        }

        return (float) (($s1 ?? 0) + ($s2 ?? 0));
    }

    /**
     * Prognosa TAHUNAN per definisi analisis: dihitung DARI nilai tahunan
     * (semester 0 / S1+S2) tiap variabel — bukan dari prognosa semester.
     */
    private function prognosaTahunan(array $def, array $nilaiMap): ?float
    {
        $tahunan = [];
        foreach (array_unique(array_merge($def['pembilang'], $def['penyebut'])) as $kode) {
            $tahunan[$kode] = $this->tahunanVariabel($nilaiMap, $kode);
        }

        return RbaAnalisisCalculator::ratio($def, $tahunan);
    }

    /**
     * Ringkasan per variabel TAHUNAN: Target diinput (tabel sendiri),
     * Realisasi tahunan (semester 0), Prognosa, Capaian — dihitung otomatis.
     */
    private function hitungVariabel(array $variables, array $nilaiMap, $variableTargets): array
    {
        $rows = [];
        foreach ($variables as $var) {
            $kode = $var['kode'];
            $target = isset($variableTargets[$kode]) ? (float) $variableTargets[$kode]->target : null;
            $realisasi = $nilaiMap[$kode][0] ?? null;
            $realisasi = $realisasi === null ? null : (float) $realisasi;
            $prognosa = RbaAnalisisCalculator::prognosa($nilaiMap[$kode][1] ?? null, $nilaiMap[$kode][2] ?? null);
            if ($prognosa === null) {
                $prognosa = $realisasi;
            }
            $rows[] = [
                'kode' => $kode,
                'label' => $var['label'],
                'grup' => $var['grup'] ?? null,
                'tipe' => $var['tipe'] ?? 'cacah',
                'target' => $target,
                'semester_1' => $nilaiMap[$kode][1] ?? null,
                'semester_2' => $nilaiMap[$kode][2] ?? null,
                'realisasi' => $realisasi,
                'prognosa' => $prognosa,
                'capaian' => RbaAnalisisCalculator::capaian($target, $prognosa),
            ];
        }

        return $rows;
    }

    private function hitung(RbaTahunAnggaran $tahun): array
    {
        $definitions = RbaAnalisisRegistry::definitions();
        $inputs = RbaAnalisisInput::where('tahun_anggaran_id', $tahun->id)->get();
        $targets = RbaAnalisisTarget::where('tahun_anggaran_id', $tahun->id)->get()->keyBy('analisis_kode');
        $variableTargets = RbaAnalisisVariableTarget::where('tahun_anggaran_id', $tahun->id)->get()->keyBy('variabel_kode');

        $nilaiMap = [];
        foreach ($inputs as $row) {
            $nilaiMap[$row->variabel_kode][(int) $row->semester] = $row->nilai === null ? null : (float) $row->nilai;
        }

        $tahunLalu = RbaTahunAnggaran::where('tahun', (int) $tahun->tahun - 1)->first();
        $nilaiMapLalu = [];
        if ($tahunLalu) {
            foreach (RbaAnalisisInput::where('tahun_anggaran_id', $tahunLalu->id)->where('semester', 0)->get() as $row) {
                $nilaiMapLalu[$row->variabel_kode] = $row->nilai === null ? null : (float) $row->nilai;
            }
        }

        return [
            'rows' => $this->hitungRows($definitions, $nilaiMap, $targets, $nilaiMapLalu, $tahunLalu),
            'variable_rows' => $this->hitungVariabel(RbaAnalisisRegistry::variables(), $nilaiMap, $variableTargets),
        ];
    }

    private function hitungRows(array $definitions, array $nilaiMap, $targets, array $nilaiMapLalu = [], $tahunLalu = null): array
    {
        $hasil = [];
        foreach ($definitions as $def) {
            $operasi = $def['operasi'] ?? 'rasio';
            if ($operasi !== 'rasio') {
                $hasil[] = $this->hitungAntarTahun($def, $nilaiMap, $targets, $nilaiMapLalu, $tahunLalu);
                continue;
            }
            $tahunan = [];
            foreach (array_unique(array_merge($def['pembilang'], $def['penyebut'])) as $kode) {
                $tahunan[$kode] = $nilaiMap[$kode][0] ?? null;
            }
            // Realisasi TAHUNAN rasio dihitung dari nilai tahunan tiap variabel
            // pembentuknya (semester 0), konsisten dengan Prognosa.
            $realisasi = RbaAnalisisCalculator::ratio($def, $tahunan);

            $target = isset($targets[$def['kode']]) ? (float) $targets[$def['kode']]->target : null;
            // Prognosa TAHUNAN dihitung dari nilai tahunan tiap variabel pembentuk
            // (target tahunan variabel / realisasi tahunan S1+S2), bukan dari
            // penjumlahan/annualisasi rasio semester — agar konsisten untuk
            // data mentah tahunan.
            $prognosa = $this->prognosaTahunan($def, $nilaiMap);

            $hasil[] = [
                'kode' => $def['kode'],
                'jenis' => $def['jenis'],
                'jenis_label' => ucfirst($def['jenis']),
                'kelompok' => $def['kelompok'] ?? $def['nama'],
                'nama' => $def['nama'],
                'satuan' => $def['satuan'],
                'tipe' => $def['tipe'] ?? 'rasio',
                'target' => $target,
                'realisasi' => $realisasi,
                'semester_1' => null,
                'semester_2' => null,
                'prognosa' => $prognosa,
                'capaian' => RbaAnalisisCalculator::capaian($target, $prognosa),
                'tahun_lalu' => $tahunLalu?->tahun,
            ];
        }

        return $hasil;
    }

    /**
     * Hitung operasi antar-tahun (PJOL, PKL, TOL):
     * bandingkan nilai tahun berjalan vs tahun lalu (semester 0).
     */
    private function hitungAntarTahun(array $def, array $nilaiMap, $targets, array $nilaiMapLalu, $tahunLalu): array
    {
        $operasi = $def['operasi'];
        $desimal = (int) ($def['desimal'] ?? 2);
        $isJumlah = str_ends_with($operasi, '_jumlah');
        $variabel = (array) ($def['variabel'] ?? []);

        $nilaiIni = $isJumlah
            ? RbaAnalisisCalculator::jumlah(array_map(fn ($k) => $nilaiMap[$k][0] ?? null, $variabel))
            : ($nilaiMap[$variabel[0] ?? ''][0] ?? null);
        $nilaiLalu = $isJumlah
            ? RbaAnalisisCalculator::jumlah(array_map(fn ($k) => $nilaiMapLalu[$k] ?? null, $variabel))
            : ($nilaiMapLalu[$variabel[0] ?? ''] ?? null);

        $realisasi = str_starts_with($operasi, 'rasio_antar_tahun')
            ? RbaAnalisisCalculator::rasioAntarTahun($nilaiIni, $nilaiLalu, $desimal)
            : RbaAnalisisCalculator::selisih($nilaiIni, $nilaiLalu, $desimal);

        $target = isset($targets[$def['kode']]) ? (float) $targets[$def['kode']]->target : null;
        // Prognosa antar-tahun = realisasi berjalan (baru 1 titik data per tahun).
        $prognosa = $realisasi;

        return [
            'kode' => $def['kode'],
            'jenis' => $def['jenis'],
            'jenis_label' => ucfirst($def['jenis']),
            'kelompok' => $def['kelompok'] ?? $def['nama'],
            'nama' => $def['nama'],
            'satuan' => $def['satuan'],
            'tipe' => $def['tipe'] ?? 'rasio',
            'target' => $target,
            'realisasi' => $realisasi,
            'semester_1' => null,
            'semester_2' => null,
            'prognosa' => $prognosa,
            'capaian' => RbaAnalisisCalculator::capaian($target, $prognosa),
            'tahun_lalu' => $tahunLalu?->tahun,
            'nilai_ini' => $nilaiIni,
            'nilai_lalu' => $nilaiLalu,
        ];
    }

    private function simpanVariableTarget(int $tahunId, array $rows, array $allowedVars, ?string $alasan, bool $isOverride): int
    {
        $saved = 0;
        foreach ($rows as $row) {
            if (! in_array($row['variabel_kode'], $allowedVars, true)) {
                continue;
            }
            $nilai = $row['target'] === null || $row['target'] === '' ? null : (float) $row['target'];

            $existing = RbaAnalisisVariableTarget::where('tahun_anggaran_id', $tahunId)
                ->where('variabel_kode', $row['variabel_kode'])
                ->first();

            if ($nilai === null) {
                if ($existing) {
                    $sebelum = $existing->getAttributes();
                    $existing->delete();
                    $this->history->record($isOverride ? 'OVERRIDE' : 'DELETE', 'rba_analisis_variable_target', $existing->id, $sebelum, null, $alasan);
                }
                continue;
            }

            if ($existing) {
                $sebelum = $existing->getAttributes();
                $existing->update(['target' => $nilai]);
                $this->history->record($isOverride ? 'OVERRIDE' : 'UPDATE', 'rba_analisis_variable_target', $existing->id, $sebelum, $existing->fresh(), $alasan);
            } else {
                $created = RbaAnalisisVariableTarget::create([
                    'tahun_anggaran_id' => $tahunId,
                    'variabel_kode' => $row['variabel_kode'],
                    'target' => $nilai,
                ]);
                $this->history->record($isOverride ? 'OVERRIDE' : 'INSERT', 'rba_analisis_variable_target', $created->id, null, $created->fresh(), $alasan);
            }
            $saved++;
        }

        return $saved;
    }

    private function simpanInput(int $tahunId, array $rows, array $allowedVars, ?string $alasan, bool $isOverride): int
    {
        $saved = 0;
        foreach ($rows as $row) {
            if (! in_array($row['variabel_kode'], $allowedVars, true)) {
                continue;
            }
            $nilai = $row['nilai'] === null || $row['nilai'] === '' ? null : (float) $row['nilai'];

            $existing = RbaAnalisisInput::where('tahun_anggaran_id', $tahunId)
                ->where('semester', $row['semester'])
                ->where('variabel_kode', $row['variabel_kode'])
                ->first();

            if ($nilai === null) {
                if ($existing) {
                    $sebelum = $existing->getAttributes();
                    $existing->delete();
                    $this->history->record($isOverride ? 'OVERRIDE' : 'DELETE', 'rba_analisis_input', $existing->id, $sebelum, null, $alasan);
                }
                continue;
            }

            if ($existing) {
                $sebelum = $existing->getAttributes();
                $existing->update(['nilai' => $nilai]);
                $this->history->record($isOverride ? 'OVERRIDE' : 'UPDATE', 'rba_analisis_input', $existing->id, $sebelum, $existing->fresh(), $alasan);
            } else {
                $created = RbaAnalisisInput::create([
                    'tahun_anggaran_id' => $tahunId,
                    'semester' => $row['semester'],
                    'variabel_kode' => $row['variabel_kode'],
                    'nilai' => $nilai,
                ]);
                $this->history->record($isOverride ? 'OVERRIDE' : 'INSERT', 'rba_analisis_input', $created->id, null, $created->fresh(), $alasan);
            }
            $saved++;
        }

        return $saved;
    }

    private function simpanTarget(int $tahunId, array $rows, array $allowedAnalisis, ?string $alasan, bool $isOverride): int
    {
        $saved = 0;
        foreach ($rows as $row) {
            if (! in_array($row['analisis_kode'], $allowedAnalisis, true)) {
                continue;
            }
            $nilai = $row['target'] === null || $row['target'] === '' ? null : (float) $row['target'];

            $existing = RbaAnalisisTarget::where('tahun_anggaran_id', $tahunId)
                ->where('analisis_kode', $row['analisis_kode'])
                ->first();

            if ($nilai === null) {
                if ($existing) {
                    $sebelum = $existing->getAttributes();
                    $existing->delete();
                    $this->history->record($isOverride ? 'OVERRIDE' : 'DELETE', 'rba_analisis_target', $existing->id, $sebelum, null, $alasan);
                }
                continue;
            }

            if ($existing) {
                $sebelum = $existing->getAttributes();
                $existing->update(['target' => $nilai]);
                $this->history->record($isOverride ? 'OVERRIDE' : 'UPDATE', 'rba_analisis_target', $existing->id, $sebelum, $existing->fresh(), $alasan);
            } else {
                $created = RbaAnalisisTarget::create([
                    'tahun_anggaran_id' => $tahunId,
                    'analisis_kode' => $row['analisis_kode'],
                    'target' => $nilai,
                ]);
                $this->history->record($isOverride ? 'OVERRIDE' : 'INSERT', 'rba_analisis_target', $created->id, null, $created->fresh(), $alasan);
            }
            $saved++;
        }

        return $saved;
    }
}
