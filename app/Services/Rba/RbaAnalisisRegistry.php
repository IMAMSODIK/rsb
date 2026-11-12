<?php

namespace App\Services\Rba;

/**
 * Registry definisi analisis RBA.
 *
 * Sumber: analisis_rba_flow.md
 * - Jenis analisis: produktivitas, efisiensi, inovasi, kesesuaian.
 * - Contoh pertama: analisis produktivitas "Rasio output layanan terhadap SDM"
 *   (ROLSDM-Total / ROLSDM-Dosen / ROLSDM-Tendik).
 *
 * Untuk menambah perhitungan analisis baru: cukup tambahkan 1 entri baru
 * ke self::definitions() — tanpa migrasi, tanpa ubah controller/view.
 */
class RbaAnalisisRegistry
{
    public const JENIS_PRODUKTIVITAS = 'produktivitas';

    public const JENIS_EFISIENSI = 'efisiensi';

    public const JENIS_INOVASI = 'inovasi';

    public const JENIS_KESESUAIAN = 'kesesuaian';

    /**
     * Variabel input mentah sesuai analisis_rba_flow.md.
     * Label tampilan memakai singkatan (PNS, PPPK, ASN, Tendik).
     *
     * @return list<array{kode: string, label: string, label_panjang: string, grup: string, tipe: string}>
     */
    public static function variables(): array
    {
        return [
            ['kode' => 'total_mahasiswa', 'label' => 'Total Mahasiswa', 'label_panjang' => 'Total Mahasiswa', 'grup' => 'Output Layanan', 'tipe' => 'cacah'],
            ['kode' => 'jlh_dosen_pns', 'label' => 'Jumlah Dosen PNS', 'label_panjang' => 'Jumlah Dosen Pegawai Negeri Sipil', 'grup' => 'Dosen/Pengajar ASN dan Non-ASN', 'tipe' => 'orang'],
            ['kode' => 'jlh_dosen_pppk', 'label' => 'Jumlah Dosen PPPK', 'label_panjang' => 'Jumlah Dosen Pegawai Pemerintah dengan Perjanjian Kerja', 'grup' => 'Dosen/Pengajar ASN dan Non-ASN', 'tipe' => 'orang'],
            ['kode' => 'jlh_dosen_non_asn', 'label' => 'Jumlah Dosen Non-ASN', 'label_panjang' => 'Jumlah Dosen Non Aparatur Sipil Negara', 'grup' => 'Dosen/Pengajar ASN dan Non-ASN', 'tipe' => 'orang'],
            ['kode' => 'tendik_pns', 'label' => 'Jumlah Tendik PNS', 'label_panjang' => 'Jumlah Tenaga Kependidikan Pegawai Negeri Sipil', 'grup' => 'Tenaga Kependidikan/Non-Pengajar ASN dan Non-ASN', 'tipe' => 'orang'],
            ['kode' => 'tendik_pppk', 'label' => 'Jumlah Tendik PPPK', 'label_panjang' => 'Jumlah Tenaga Kependidikan Pegawai Pemerintah dengan Perjanjian Kerja', 'grup' => 'Tenaga Kependidikan/Non-Pengajar ASN dan Non-ASN', 'tipe' => 'orang'],
            ['kode' => 'tendik_non_asn', 'label' => 'Jumlah Tendik Non-ASN', 'label_panjang' => 'Jumlah Tenaga Kependidikan Non Aparatur Sipil Negara', 'grup' => 'Tenaga Kependidikan/Non-Pengajar ASN dan Non-ASN', 'tipe' => 'orang'],
            ['kode' => 'pnbp_layanan_utama', 'label' => 'PNBP Layanan Utama', 'label_panjang' => 'Pendapatan Negara Bukan Pajak Badan Layanan Umum Layanan Utama', 'grup' => 'Data PNBP BLU', 'tipe' => 'currency'],
            ['kode' => 'pnbp_pengelolaan_kas', 'label' => 'PNBP Pengelolaan Kas', 'label_panjang' => 'Pendapatan Negara Bukan Pajak Badan Layanan Umum Pengelolaan Kas', 'grup' => 'Data PNBP BLU', 'tipe' => 'currency'],
            ['kode' => 'pnbp_optimalisasi_aset', 'label' => 'PNBP Optimalisasi Aset dan Kerja Sama', 'label_panjang' => 'Pendapatan Negara Bukan Pajak Badan Layanan Umum Optimalisasi Aset Tetap dan Kerja Sama', 'grup' => 'Data PNBP BLU', 'tipe' => 'currency'],
            ['kode' => 'pnbp_unit_bisnis', 'label' => 'PNBP Unit Bisnis', 'label_panjang' => 'Pendapatan Negara Bukan Pajak Badan Layanan Umum Unit Bisnis', 'grup' => 'Data PNBP BLU', 'tipe' => 'currency'],
            ['kode' => 'pnbp_hibah', 'label' => 'PNBP Hibah', 'label_panjang' => 'Pendapatan Negara Bukan Pajak Badan Layanan Umum Hibah', 'grup' => 'Data PNBP BLU', 'tipe' => 'currency'],
            ['kode' => 'skor_ikm', 'label' => 'Skor IKM', 'label_panjang' => 'Skor Indeks Kepuasan Masyarakat', 'grup' => 'Data Kualitas Layanan', 'tipe' => 'desimal'],
            ['kode' => 'skor_maturity_rating', 'label' => 'Skor Maturity Rating', 'label_panjang' => 'Skor Maturity Rating', 'grup' => 'Data Kualitas Layanan', 'tipe' => 'desimal'],
        ];
    }

    /**
     * Semua definisi perhitungan analisis.
     * Label/satuan SELALU nama lengkap tanpa singkatan.
     *
     * @return list<array{kode: string, jenis: string, kelompok: string, nama: string, pembilang: list<string>, penyebut: list<string>, satuan: string, tipe: string, desimal: int}>
     */
    public static function definitions(): array
    {
        return [
            [
                'kode' => 'ROLSDM-Total',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Rasio Output Layanan terhadap Sumber Daya Manusia',
                'nama' => 'Rasio Output Layanan terhadap Sumber Daya Manusia Total',
                'pembilang' => ['total_mahasiswa'],
                'penyebut' => ['jlh_dosen_pns', 'jlh_dosen_pppk', 'jlh_dosen_non_asn', 'tendik_pns', 'tendik_pppk', 'tendik_non_asn'],
                'satuan' => 'Mahasiswa per Sumber Daya Manusia',
                'tipe' => 'rasio',
                'desimal' => 4,
            ],
            [
                'kode' => 'ROLSDM-Dosen',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Rasio Output Layanan terhadap Sumber Daya Manusia',
                'nama' => 'Rasio Output Layanan terhadap Sumber Daya Manusia Dosen',
                'pembilang' => ['total_mahasiswa'],
                'penyebut' => ['jlh_dosen_pns', 'jlh_dosen_pppk', 'jlh_dosen_non_asn'],
                'satuan' => 'Mahasiswa per Dosen',
                'tipe' => 'rasio',
                'desimal' => 4,
            ],
            [
                'kode' => 'ROLSDM-Tendik',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Rasio Output Layanan terhadap Sumber Daya Manusia',
                'nama' => 'Rasio Output Layanan terhadap Sumber Daya Manusia Tenaga Kependidikan',
                'pembilang' => ['total_mahasiswa'],
                'penyebut' => ['tendik_pns', 'tendik_pppk', 'tendik_non_asn'],
                'satuan' => 'Mahasiswa per Tenaga Kependidikan',
                'tipe' => 'rasio',
                'desimal' => 4,
            ],
            [
                'kode' => 'RPSDM-Total',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Rasio Pendapatan Penerimaan Negara Bukan Pajak Badan Layanan Umum terhadap Sumber Daya Manusia',
                'nama' => 'Rasio Pendapatan Penerimaan Negara Bukan Pajak Badan Layanan Umum terhadap Sumber Daya Manusia Total',
                'pembilang' => ['pnbp_layanan_utama', 'pnbp_pengelolaan_kas', 'pnbp_optimalisasi_aset', 'pnbp_unit_bisnis', 'pnbp_hibah'],
                'penyebut' => ['jlh_dosen_pns', 'jlh_dosen_pppk', 'jlh_dosen_non_asn', 'tendik_pns', 'tendik_pppk', 'tendik_non_asn'],
                'satuan' => 'Rupiah per Sumber Daya Manusia',
                'tipe' => 'currency',
                'desimal' => 2,
            ],
            [
                'kode' => 'RPSDM-Dosen',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Rasio Pendapatan Penerimaan Negara Bukan Pajak Badan Layanan Umum terhadap Sumber Daya Manusia',
                'nama' => 'Rasio Pendapatan Penerimaan Negara Bukan Pajak Badan Layanan Umum terhadap Sumber Daya Manusia Dosen',
                'pembilang' => ['pnbp_layanan_utama', 'pnbp_pengelolaan_kas', 'pnbp_optimalisasi_aset', 'pnbp_unit_bisnis', 'pnbp_hibah'],
                'penyebut' => ['jlh_dosen_pns', 'jlh_dosen_pppk', 'jlh_dosen_non_asn'],
                'satuan' => 'Rupiah per Dosen',
                'tipe' => 'currency',
                'desimal' => 2,
            ],
            [
                'kode' => 'RPSDM-Tendik',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Rasio Pendapatan Penerimaan Negara Bukan Pajak Badan Layanan Umum terhadap Sumber Daya Manusia',
                'nama' => 'Rasio Pendapatan Penerimaan Negara Bukan Pajak Badan Layanan Umum terhadap Sumber Daya Manusia Tenaga Kependidikan',
                'pembilang' => ['pnbp_layanan_utama', 'pnbp_pengelolaan_kas', 'pnbp_optimalisasi_aset', 'pnbp_unit_bisnis', 'pnbp_hibah'],
                'penyebut' => ['tendik_pns', 'tendik_pppk', 'tendik_non_asn'],
                'satuan' => 'Rupiah per Tenaga Kependidikan',
                'tipe' => 'currency',
                'desimal' => 2,
            ],
            [
                'kode' => 'RPOA',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Rasio Pendapatan Penerimaan Negara Bukan Pajak Badan Layanan Umum hasil Optimalisasi Aset',
                'nama' => 'Rasio Pendapatan Penerimaan Negara Bukan Pajak Badan Layanan Umum hasil Optimalisasi Aset',
                'pembilang' => ['pnbp_pengelolaan_kas', 'pnbp_optimalisasi_aset', 'pnbp_unit_bisnis'],
                'penyebut' => ['pnbp_layanan_utama', 'pnbp_pengelolaan_kas', 'pnbp_optimalisasi_aset', 'pnbp_unit_bisnis', 'pnbp_hibah'],
                'satuan' => 'Persen',
                'tipe' => 'persen',
                'desimal' => 2,
            ],
            [
                'kode' => 'PJOL-Mahasiswa',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Peningkatan Jumlah Output Layanan',
                'nama' => 'Peningkatan Jumlah Output Layanan Mahasiswa',
                'operasi' => 'selisih_antar_tahun',
                'variabel' => 'total_mahasiswa',
                'satuan' => 'Mahasiswa',
                'tipe' => 'cacah',
                'desimal' => 0,
            ],
            [
                'kode' => 'PJOL-Pendapatan',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Peningkatan Jumlah Output Layanan',
                'nama' => 'Peningkatan Jumlah Output Layanan Pendapatan',
                'operasi' => 'selisih_antar_tahun_jumlah',
                'variabel' => ['pnbp_layanan_utama', 'pnbp_pengelolaan_kas', 'pnbp_optimalisasi_aset', 'pnbp_unit_bisnis', 'pnbp_hibah'],
                'satuan' => 'Rupiah',
                'tipe' => 'currency',
                'desimal' => 2,
            ],
            [
                'kode' => 'PKL-IKM',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Peningkatan Kualitas Layanan hasil Survei Indeks Kepuasan Masyarakat',
                'nama' => 'Peningkatan Kualitas Layanan Indeks Kepuasan Masyarakat',
                'operasi' => 'selisih_antar_tahun',
                'variabel' => 'skor_ikm',
                'satuan' => 'Skor',
                'tipe' => 'desimal',
                'desimal' => 2,
            ],
            [
                'kode' => 'PKL-Maturity',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Peningkatan Kualitas Layanan hasil Survei Indeks Kepuasan Masyarakat',
                'nama' => 'Peningkatan Kualitas Layanan Maturity Rating',
                'operasi' => 'selisih_antar_tahun',
                'variabel' => 'skor_maturity_rating',
                'satuan' => 'Skor',
                'tipe' => 'desimal',
                'desimal' => 2,
            ],
            [
                'kode' => 'TOL-Mahasiswa',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Target Output Layanan',
                'nama' => 'Target Output Layanan Mahasiswa',
                'operasi' => 'rasio_antar_tahun',
                'variabel' => 'total_mahasiswa',
                'satuan' => 'Indeks',
                'tipe' => 'rasio',
                'desimal' => 4,
            ],
            [
                'kode' => 'TOL-Pendapatan',
                'jenis' => self::JENIS_PRODUKTIVITAS,
                'kelompok' => 'Target Output Layanan',
                'nama' => 'Target Output Layanan Pendapatan',
                'operasi' => 'rasio_antar_tahun_jumlah',
                'variabel' => ['pnbp_layanan_utama', 'pnbp_pengelolaan_kas', 'pnbp_optimalisasi_aset', 'pnbp_unit_bisnis', 'pnbp_hibah'],
                'satuan' => 'Indeks',
                'tipe' => 'rasio',
                'desimal' => 4,
            ],
        ];
    }

    /** @return list<string> */
    public static function jenisList(): array
    {
        return [self::JENIS_PRODUKTIVITAS, self::JENIS_EFISIENSI, self::JENIS_INOVASI, self::JENIS_KESESUAIAN];
    }

    /** @return array<string, string> kode => label variabel */
    public static function variableLabels(): array
    {
        $labels = [];
        foreach (self::variables() as $var) {
            $labels[$var['kode']] = $var['label'];
        }

        return $labels;
    }

    public static function find(string $kode): ?array
    {
        foreach (self::definitions() as $def) {
            if ($def['kode'] === $kode) {
                return $def;
            }
        }

        return null;
    }
}
