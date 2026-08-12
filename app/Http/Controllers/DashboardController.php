<?php

namespace App\Http\Controllers;

use App\Models\IkuKinerjaLayanan;
use App\Models\KategoriKinerjaLayanan;
use App\Models\PeriodeRsb;
use App\Models\RealisasiKinerjaIkuRsb;
use App\Models\RealisasiKinerjaLayanan;
use App\Models\TargetKinerjaIkuRsb;
use App\Models\TargetKinerjaLayanan;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $periodeAktif = PeriodeRsb::where('is_active', true)->first();

        if ($user->role === 'admin') {
            return view('dashboard.index', $this->adminDashboard($periodeAktif));
        }

        return view('dashboard.index', $this->operatorDashboard($periodeAktif, $user->id));
    }

    private function adminDashboard(?PeriodeRsb $periodeAktif): array
    {
        $units = UnitKerja::with('operator')->orderBy('kode_unit')->get();
        $kategori = KategoriKinerjaLayanan::get(['id', 'unit_kerja_id']);
        $iku = IkuKinerjaLayanan::get(['id', 'unit_kerja_id']);
        $serviceTargets = $this->serviceTargets($periodeAktif);
        $ikuTargets = $this->ikuTargets($periodeAktif);
        $serviceRealisasi = $this->serviceRealisasi($serviceTargets);
        $ikuRealisasi = $this->ikuRealisasi($ikuTargets);

        $unitSummary = $units->map(function (UnitKerja $unit) use ($kategori, $iku, $serviceTargets, $ikuTargets, $serviceRealisasi, $ikuRealisasi) {
            $categoryCount = $kategori->where('unit_kerja_id', $unit->id)->count();
            $ikuCount = $iku->where('unit_kerja_id', $unit->id)->count();
            $serviceTargetFilled = $serviceTargets->where('unit_kerja_id', $unit->id)
                ->filter(fn ($target) => $this->hasTargetValues($target->target_tahunan))->count();
            $ikuTargetFilled = $ikuTargets->where('unit_kerja_id', $unit->id)
                ->filter(fn ($target) => $this->hasTargetValues($target->target_tahunan))->count();
            $serviceRealizationFilled = $serviceRealisasi->filter(fn ($item) => $item->target?->unit_kerja_id === $unit->id)
                ->filter(fn ($item) => $this->hasRealizationValues($item->realisasi_tahunan))->count();
            $ikuRealizationFilled = $ikuRealisasi->filter(fn ($item) => $item->target?->unit_kerja_id === $unit->id)
                ->filter(fn ($item) => $this->hasRealizationValues($item->realisasi_tahunan))->count();

            return [
                'unit' => $unit->kode_unit.' - '.$unit->nama_unit,
                'operator' => $unit->operator?->name ?? '-',
                'kategori' => $categoryCount,
                'iku' => $ikuCount,
                'target_layanan' => $this->progress($serviceTargetFilled, $categoryCount),
                'realisasi_layanan' => $this->progress($serviceRealizationFilled, $categoryCount),
                'target_iku' => $this->progress($ikuTargetFilled, $ikuCount),
                'realisasi_iku' => $this->progress($ikuRealizationFilled, $ikuCount),
            ];
        });

        return [
            'isAdmin' => true,
            'user' => auth()->user(),
            'periodeAktif' => $periodeAktif,
            'stats' => [
                ['label' => 'Unit Kerja', 'value' => $units->count(), 'icon' => 'bi-building', 'color' => 'primary'],
                ['label' => 'Operator', 'value' => User::where('role', 'operator')->count(), 'icon' => 'bi-people', 'color' => 'success'],
                ['label' => 'Kategori Layanan', 'value' => $kategori->count(), 'icon' => 'bi-diagram-3', 'color' => 'warning'],
                ['label' => 'IKU RSB', 'value' => $iku->count(), 'icon' => 'bi-graph-up-arrow', 'color' => 'info'],
            ],
            'summary' => $this->summary($kategori->count(), $iku->count(), $serviceTargets, $ikuTargets, $serviceRealisasi, $ikuRealisasi),
            'unitSummary' => $unitSummary,
        ];
    }

    private function operatorDashboard(?PeriodeRsb $periodeAktif, int $userId): array
    {
        $unit = UnitKerja::where('operator_id', $userId)->first();
        $kategori = $unit ? KategoriKinerjaLayanan::where('unit_kerja_id', $unit->id)->get(['id', 'unit_kerja_id']) : collect();
        $iku = $unit ? IkuKinerjaLayanan::where('unit_kerja_id', $unit->id)->get(['id', 'unit_kerja_id']) : collect();
        $serviceTargets = $this->serviceTargets($periodeAktif, $unit?->id);
        $ikuTargets = $this->ikuTargets($periodeAktif, $unit?->id);
        $serviceRealisasi = $this->serviceRealisasi($serviceTargets);
        $ikuRealisasi = $this->ikuRealisasi($ikuTargets);

        return [
            'isAdmin' => false,
            'user' => auth()->user(),
            'unit' => $unit,
            'periodeAktif' => $periodeAktif,
            'stats' => [
                ['label' => 'Kategori Layanan', 'value' => $kategori->count(), 'icon' => 'bi-diagram-3', 'color' => 'primary'],
                ['label' => 'IKU RSB', 'value' => $iku->count(), 'icon' => 'bi-graph-up-arrow', 'color' => 'success'],
                ['label' => 'Target Terisi', 'value' => $this->filledTargetCount($serviceTargets) + $this->filledTargetCount($ikuTargets), 'icon' => 'bi-bullseye', 'color' => 'warning'],
                ['label' => 'Realisasi Terisi', 'value' => $this->filledRealizationCount($serviceRealisasi) + $this->filledRealizationCount($ikuRealisasi), 'icon' => 'bi-check2-circle', 'color' => 'info'],
            ],
            'summary' => $this->summary($kategori->count(), $iku->count(), $serviceTargets, $ikuTargets, $serviceRealisasi, $ikuRealisasi),
        ];
    }

    private function serviceTargets(?PeriodeRsb $periodeAktif, ?int $unitId = null): Collection
    {
        if (!$periodeAktif) {
            return collect();
        }

        return TargetKinerjaLayanan::where('periode_rsb_id', $periodeAktif->id)
            ->when($unitId, fn ($query) => $query->where('unit_kerja_id', $unitId))
            ->get();
    }

    private function ikuTargets(?PeriodeRsb $periodeAktif, ?int $unitId = null): Collection
    {
        if (!$periodeAktif) {
            return collect();
        }

        return TargetKinerjaIkuRsb::where('periode_rsb_id', $periodeAktif->id)
            ->when($unitId, fn ($query) => $query->where('unit_kerja_id', $unitId))
            ->get();
    }

    private function serviceRealisasi(Collection $targets): Collection
    {
        return RealisasiKinerjaLayanan::with('target')
            ->whereIn('target_kinerja_layanan_id', $targets->pluck('id'))
            ->get();
    }

    private function ikuRealisasi(Collection $targets): Collection
    {
        return RealisasiKinerjaIkuRsb::with('target')
            ->whereIn('target_kinerja_iku_rsb_id', $targets->pluck('id'))
            ->get();
    }

    private function summary(int $categoryCount, int $ikuCount, Collection $serviceTargets, Collection $ikuTargets, Collection $serviceRealisasi, Collection $ikuRealisasi): array
    {
        return [
            [
                'label' => 'Kinerja Layanan',
                'target' => $this->progress($this->filledTargetCount($serviceTargets), $categoryCount),
                'realisasi' => $this->progress($this->filledRealizationCount($serviceRealisasi), $categoryCount),
            ],
            [
                'label' => 'Kinerja IKU RSB',
                'target' => $this->progress($this->filledTargetCount($ikuTargets), $ikuCount),
                'realisasi' => $this->progress($this->filledRealizationCount($ikuRealisasi), $ikuCount),
            ],
        ];
    }

    private function filledTargetCount(Collection $targets): int
    {
        return $targets->filter(fn ($target) => $this->hasTargetValues($target->target_tahunan))->count();
    }

    private function filledRealizationCount(Collection $realisasi): int
    {
        return $realisasi->filter(fn ($item) => $this->hasRealizationValues($item->realisasi_tahunan))->count();
    }

    private function progress(int $filled, int $total): array
    {
        return ['filled' => $filled, 'total' => $total, 'percentage' => $total > 0 ? round(($filled / $total) * 100) : 0];
    }

    private function hasTargetValues(?array $values): bool
    {
        foreach ($values ?? [] as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    private function hasRealizationValues(?array $values): bool
    {
        foreach ($values ?? [] as $value) {
            if (is_array($value) && array_key_exists('nilai', $value) && $value['nilai'] !== null && $value['nilai'] !== '') {
                return true;
            }
            if (is_array($value) && $this->hasRealizationValues($value)) {
                return true;
            }
        }

        return false;
    }
}
