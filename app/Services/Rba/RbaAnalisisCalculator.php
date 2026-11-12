<?php

namespace App\Services\Rba;

/**
 * Kalkulator analisis RBA — murni fungsi hitung, tanpa I/O database.
 *
 * Aturan dari analisis_rba_flow.md:
 * - Target diinput manual.
 * - Realisasi DIHITUNG dari data mentah yang diinput (bukan diinput).
 * - Prognosa dihasilkan dari target + realisasi berjalan.
 *
 * Prognosa tahunan (aturan sederhana & eksplisit per semester):
 * - Jika semester 2 sudah ada  => prognosa = realisasi semester 1 + realisasi semester 2.
 * - Jika baru semester 1       => prognosa = realisasi S1 x 2 (annualisasi linier).
 * - Jika belum ada realisasi   => prognosa = null.
 */
class RbaAnalisisCalculator
{
    /**
     * Hitung satu nilai rasio: sum(pembilang) / sum(penyebut).
     *
     * @param  array<string, float|null>  $values  variabel_kode => nilai (null = belum diisi)
     */
    public static function ratio(array $definition, array $values): ?float
    {
        $atas = 0.0;
        foreach ($definition['pembilang'] as $kode) {
            if (! array_key_exists($kode, $values) || $values[$kode] === null) {
                return null;
            }
            $atas += (float) $values[$kode];
        }

        $bawah = 0.0;
        foreach ($definition['penyebut'] as $kode) {
            if (! array_key_exists($kode, $values) || $values[$kode] === null) {
                return null;
            }
            $bawah += (float) $values[$kode];
        }

        if ($bawah == 0.0) {
            return null;
        }

        return round($atas / $bawah, (int) ($definition['desimal'] ?? 4));
    }

    /**
     * Jumlah ketat (strict): null jika ada satu pun komponen kosong.
     *
     * @param  array<string, float|null>  $values
     */
    public static function jumlah(array $values): ?float
    {
        $total = 0.0;
        foreach ($values as $v) {
            if ($v === null) {
                return null;
            }
            $total += (float) $v;
        }

        return $total;
    }

    /**
     * Selisih antar-tahun: nilai tahun berjalan - nilai tahun lalu.
     * Dipakai PJOL (peningkatan jumlah) & PKL (peningkatan kualitas).
     */
    public static function selisih(?float $tahunIni, ?float $tahunLalu, int $desimal = 2): ?float
    {
        if ($tahunIni === null || $tahunLalu === null) {
            return null;
        }

        return round($tahunIni - $tahunLalu, $desimal);
    }

    /**
     * Rasio antar-tahun: nilai tahun berjalan / nilai tahun lalu.
     * Dipakai TOL (target output layanan sebagai indeks, mis. 1,05).
     */
    public static function rasioAntarTahun(?float $tahunIni, ?float $tahunLalu, int $desimal = 4): ?float
    {
        if ($tahunIni === null || $tahunLalu === null || $tahunLalu == 0.0) {
            return null;
        }

        return round($tahunIni / $tahunLalu, $desimal);
    }
    public static function prognosa(?float $semester1, ?float $semester2): ?float
    {
        if ($semester1 !== null && $semester2 !== null) {
            return round($semester1 + $semester2, 4);
        }

        if ($semester1 !== null && $semester2 === null) {
            return round($semester1 * 2, 4);
        }

        return null;
    }

    /**
     * Capaian prognosa terhadap target dalam persen.
     */
    public static function capaian(?float $target, ?float $prognosa): ?float
    {
        if ($target === null || $prognosa === null || $target == 0.0) {
            return null;
        }

        return round(($prognosa / $target) * 100, 2);
    }
}
