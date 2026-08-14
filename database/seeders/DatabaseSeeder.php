<?php

namespace Database\Seeders;

use App\Models\IkuKinerjaLayanan;
use App\Models\JenisKinerjaLayanan;
use App\Models\KategoriKinerjaLayanan;
use App\Models\PeriodeRsb;
use App\Models\SasaranProgram;
use App\Models\SasaranStrategis;
use App\Models\SliderItem;
use App\Models\TujuanRsb;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = [];
        foreach ([
            ['name' => 'Super Admin', 'email' => 'admin@gmail.com', 'role' => 'admin'],
            ['name' => 'Operator Akademik', 'email' => 'operator.akademik@gmail.com', 'role' => 'operator'],
            ['name' => 'Operator Keuangan', 'email' => 'operator.keuangan@gmail.com', 'role' => 'operator'],
            ['name' => 'Operator Kemahasiswaan', 'email' => 'operator.kemahasiswaan@gmail.com', 'role' => 'operator'],
            ['name' => 'Operator Umum', 'email' => 'operator.umum@gmail.com', 'role' => 'operator'],
        ] as $userData) {
            $users[$userData['email']] = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('123456'),
                    'role' => $userData['role'],
                ]
            );
        }

        $units = [];
        foreach ([
            ['kode_unit' => 'UK-01', 'nama_unit' => 'Biro Akademik', 'operator' => 'operator.akademik@gmail.com'],
            ['kode_unit' => 'UK-02', 'nama_unit' => 'Biro Keuangan', 'operator' => 'operator.keuangan@gmail.com'],
            ['kode_unit' => 'UK-03', 'nama_unit' => 'Biro Kemahasiswaan', 'operator' => 'operator.kemahasiswaan@gmail.com'],
            ['kode_unit' => 'UK-04', 'nama_unit' => 'Biro Umum', 'operator' => 'operator.umum@gmail.com'],
            ['kode_unit' => 'UK-05', 'nama_unit' => 'Lembaga Penjaminan Mutu', 'operator' => 'admin@gmail.com'],
        ] as $unitData) {
            $units[$unitData['kode_unit']] = UnitKerja::updateOrCreate(
                ['kode_unit' => $unitData['kode_unit']],
                [
                    'nama_unit' => $unitData['nama_unit'],
                    'operator_id' => $users[$unitData['operator']]->id,
                ]
            );
        }

        $jenisLayanan = [];
        foreach ([
            ['kode_layanan' => 'LYN-01', 'uraian' => 'Pendidikan dan Pengajaran'],
            ['kode_layanan' => 'LYN-02', 'uraian' => 'Penelitian'],
            ['kode_layanan' => 'LYN-03', 'uraian' => 'Pengabdian kepada Masyarakat'],
            ['kode_layanan' => 'LYN-04', 'uraian' => 'Kemahasiswaan'],
            ['kode_layanan' => 'LYN-05', 'uraian' => 'Administrasi dan Umum'],
        ] as $layananData) {
            $jenisLayanan[$layananData['kode_layanan']] = JenisKinerjaLayanan::updateOrCreate(
                ['kode_layanan' => $layananData['kode_layanan']],
                ['uraian' => $layananData['uraian']]
            );
        }

        $kategori = [];
        $uraianKategori = [
            'Mutu Layanan Pendidikan',
            'Produktivitas Penelitian',
            'Dampak Pengabdian Masyarakat',
            'Prestasi dan Layanan Kemahasiswaan',
            'Kualitas Tata Kelola',
        ];
        $uraianKategoriKemahasiswaan = [
            'Layanan Informasi Kemahasiswaan',
            'Layanan Beasiswa Mahasiswa',
            'Layanan Bantuan Biaya Pendidikan',
            'Pembinaan Organisasi Mahasiswa',
            'Pembinaan UKM dan Olahraga',
            'Pembinaan Seni dan Budaya',
            'Pengembangan Kaderisasi Mahasiswa',
            'Layanan Prestasi Mahasiswa',
            'Pendampingan Lomba Ilmiah Mahasiswa',
            'Program Kreativitas Mahasiswa',
            'Layanan Magang Mahasiswa',
            'Layanan Kewirausahaan Mahasiswa',
            'Program Pertukaran Mahasiswa',
            'Layanan Kesejahteraan Mahasiswa',
            'Layanan Konseling Mahasiswa',
            'Layanan Kesehatan Mahasiswa',
            'Layanan Asrama dan Perumahan',
            'Tracer Study dan Karier Alumni',
            'Layanan Parkir dan Transportasi Mahasiswa',
            'Layanan Aktivitas dan Dispensasi Mahasiswa',
        ];
        $unitCodes = array_keys($units);
        $nomorKategori = 1;
        foreach ($unitCodes as $unitCode) {
            $daftarUraian = $unitCode === 'UK-03' ? $uraianKategoriKemahasiswaan : $uraianKategori;
            foreach ($daftarUraian as $layananIndex => $uraian) {
                $kodeLayanan = $unitCode === 'UK-03'
                    ? 'LYN-04'
                    : 'LYN-'.str_pad((string) ($layananIndex + 1), 2, '0', STR_PAD_LEFT);
                $kategoriData = [
                    'kode_kategori' => 'KAT-'.str_pad((string) $nomorKategori, 2, '0', STR_PAD_LEFT),
                    'uraian' => $uraian,
                    'layanan' => $kodeLayanan,
                    'unit' => $unitCode,
                ];
            $kategori[$kategoriData['kode_kategori']] = KategoriKinerjaLayanan::updateOrCreate(
                ['kode_kategori' => $kategoriData['kode_kategori']],
                [
                    'layanan_id' => $jenisLayanan[$kategoriData['layanan']]->id,
                    'uraian' => $kategoriData['uraian'],
                    'unit_kerja_id' => $units[$kategoriData['unit']]->id,
                ]
            );
            $nomorKategori++;
            }
        }

        $tujuan = [];
        foreach ([
            ['kode_tujuan' => 'T1', 'uraian' => 'Meningkatkan mutu pendidikan tinggi yang unggul'],
            ['kode_tujuan' => 'T2', 'uraian' => 'Menguatkan budaya riset dan inovasi'],
            ['kode_tujuan' => 'T3', 'uraian' => 'Meningkatkan kontribusi kepada masyarakat'],
            ['kode_tujuan' => 'T4', 'uraian' => 'Mengembangkan prestasi dan kesejahteraan mahasiswa'],
            ['kode_tujuan' => 'T5', 'uraian' => 'Mewujudkan tata kelola organisasi yang efektif'],
        ] as $tujuanData) {
            $tujuan[$tujuanData['kode_tujuan']] = TujuanRsb::updateOrCreate(
                ['kode_tujuan' => $tujuanData['kode_tujuan']],
                ['uraian' => $tujuanData['uraian']]
            );
        }

        $sasaranProgram = [];
        foreach ([
            ['kode' => 'T1.SP1', 'uraian' => 'Peningkatan kualitas proses pembelajaran', 'tujuan' => 'T1'],
            ['kode' => 'T2.SP1', 'uraian' => 'Peningkatan produktivitas penelitian dan inovasi', 'tujuan' => 'T2'],
            ['kode' => 'T3.SP1', 'uraian' => 'Penguatan program pengabdian berbasis kebutuhan masyarakat', 'tujuan' => 'T3'],
            ['kode' => 'T4.SP1', 'uraian' => 'Peningkatan layanan dan prestasi kemahasiswaan', 'tujuan' => 'T4'],
            ['kode' => 'T5.SP1', 'uraian' => 'Penguatan sistem tata kelola dan layanan administrasi', 'tujuan' => 'T5'],
        ] as $programData) {
            $sasaranProgram[$programData['kode']] = SasaranProgram::updateOrCreate(
                ['kode_sasaran_program' => $programData['kode']],
                [
                    'tujuan_id' => $tujuan[$programData['tujuan']]->id,
                    'uraian' => $programData['uraian'],
                ]
            );
        }

        $sasaranStrategis = [];
        foreach ([
            ['kode' => 'T1.SP1.SS1', 'uraian' => 'Kurikulum dan pembelajaran berorientasi mutu', 'program' => 'T1.SP1'],
            ['kode' => 'T2.SP1.SS1', 'uraian' => 'Ekosistem riset yang produktif dan kolaboratif', 'program' => 'T2.SP1'],
            ['kode' => 'T3.SP1.SS1', 'uraian' => 'Pengabdian masyarakat yang berdampak', 'program' => 'T3.SP1'],
            ['kode' => 'T4.SP1.SS1', 'uraian' => 'Mahasiswa berprestasi dan berdaya saing', 'program' => 'T4.SP1'],
            ['kode' => 'T5.SP1.SS1', 'uraian' => 'Tata kelola yang transparan dan akuntabel', 'program' => 'T5.SP1'],
        ] as $strategisData) {
            $sasaranStrategis[$strategisData['kode']] = SasaranStrategis::updateOrCreate(
                ['kode_sasaran_strategis' => $strategisData['kode']],
                [
                    'sasaran_program_id' => $sasaranProgram[$strategisData['program']]->id,
                    'uraian' => $strategisData['uraian'],
                ]
            );
        }

        foreach ([
            ['kode' => 'T1.SP1.SS1.IKU1', 'uraian' => 'Persentase kelulusan tepat waktu', 'strategis' => 'T1.SP1.SS1', 'unit' => 'UK-01'],
            ['kode' => 'T2.SP1.SS1.IKU1', 'uraian' => 'Jumlah publikasi ilmiah bereputasi', 'strategis' => 'T2.SP1.SS1', 'unit' => 'UK-02'],
            ['kode' => 'T3.SP1.SS1.IKU1', 'uraian' => 'Persentase program pengabdian berkelanjutan', 'strategis' => 'T3.SP1.SS1', 'unit' => 'UK-03'],
            ['kode' => 'T4.SP1.SS1.IKU1', 'uraian' => 'Jumlah prestasi mahasiswa tingkat nasional', 'strategis' => 'T4.SP1.SS1', 'unit' => 'UK-04'],
            ['kode' => 'T5.SP1.SS1.IKU1', 'uraian' => 'Indeks kepuasan layanan administrasi', 'strategis' => 'T5.SP1.SS1', 'unit' => 'UK-05'],
        ] as $ikuData) {
            IkuKinerjaLayanan::updateOrCreate(
                ['kode_iku' => $ikuData['kode']],
                [
                    'sasaran_strategis_id' => $sasaranStrategis[$ikuData['strategis']]->id,
                    'unit_kerja_id' => $units[$ikuData['unit']]->id,
                    'uraian' => $ikuData['uraian'],
                ]
            );
        }

        foreach ([
            ['tahun_awal' => 2025, 'tahun_akhir' => 2029, 'is_active' => true],
            ['tahun_awal' => 2020, 'tahun_akhir' => 2024, 'is_active' => false],
            ['tahun_awal' => 2015, 'tahun_akhir' => 2019, 'is_active' => false],
            ['tahun_awal' => 2010, 'tahun_akhir' => 2014, 'is_active' => false],
            ['tahun_awal' => 2005, 'tahun_akhir' => 2009, 'is_active' => false],
        ] as $periodeData) {
            PeriodeRsb::updateOrCreate(
                [
                    'tahun_awal' => $periodeData['tahun_awal'],
                    'tahun_akhir' => $periodeData['tahun_akhir'],
                ],
                ['is_active' => $periodeData['is_active']]
            );
        }

        foreach ([
            ['urutan' => 1, 'judul' => 'Kampus Modern', 'gambar' => 'https://images.unsplash.com/photo-1564981797816-1043664bf78d?w=1600'],
            ['urutan' => 2, 'judul' => 'Auditorium', 'gambar' => 'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?w=1600'],
            ['urutan' => 3, 'judul' => 'Mahasiswa Berprestasi', 'gambar' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1600'],
            ['urutan' => 4, 'judul' => 'Riset dan Inovasi', 'gambar' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=1600'],
            ['urutan' => 5, 'judul' => 'Layanan Terintegrasi', 'gambar' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1600'],
        ] as $sliderData) {
            SliderItem::updateOrCreate(
                ['urutan' => $sliderData['urutan']],
                [
                    'judul' => $sliderData['judul'],
                    'gambar' => $sliderData['gambar'],
                    'is_active' => true,
                ]
            );
        }
    }
}
