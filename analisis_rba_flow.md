# Alur Analisis RBA (analisis_rba_flow.md)

Dokumen ini adalah acuan TETAP untuk menu Analisis RBA.
Semua jenis analisis & variabel baru yang ditambahkan kemudian
WAJIB mengikuti aturan di sini.

---

## 1. Prinsip Umum

1. Target DIINPUT manual per tahun.
2. Realisasi DIHITUNG dari data yang diinput (bukan diinput langsung
   sebagai angka jadi), kecuali Realisasi Tahunan variabel yang
   diinput sebagai angka tahunan (semester 0).
3. Prognosa DIHASILKAN otomatis dari Target + Realisasi berjalan.
4. Capaian = (Prognosa / Target) x 100%.

## 2. Struktur Halaman (disepakati)

- Baris 1: judul "Analisis RBA (Target, Realisasi & Prognosa)".
- Baris 2: filter 2 kolom berdampingan — Tahun (kiri) + Jenis (kanan).
- Kartu ringkasan KPI di atas tab:
  Jumlah Analisis | Rata-rata Capaian | Prognosa Terisi (x/y) |
  Variabel: target terisi / realisasi terisi.
- Tab 1 "Hasil Analisis" (fokus utama, langsung terlihat saat buka):
  Nomor | Kode | Nama Analisis | Target (tahunan, input) |
  Realisasi (tahunan, hitung) | Prognosa (hitung) | Capaian % (hitung).
  DIKELOMPOKKAN per Jenis Analisis + Kelompok Rasio: tiap kelompok
  tampil sebagai kartu (card) dengan header badge Jenis + nama
  Kelompok Rasio + badge jumlah analisis. Penomoran berlanjut
  antar kelompok. Filter Jenis tetap memfilter kelompok.
  Kolom Nomor + Kode STICKY saat scroll horizontal.
  TIDAK ada kolom Rumus dan TIDAK ada kolom Satuan di tabel.
- Tab 2 "Data Mentah" (input per variabel, tahunan):
  Nomor | Variabel | Target (tahunan, input) |
  Realisasi (tahunan, input semester 0) |
  Prognosa (hitung) | Capaian % (hitung).
  Kolom Nomor + Variabel STICKY saat scroll horizontal.
  Kolom Variabel min-width 220px. Ada pencarian variabel.
- Tombol Simpan TIDAK ADA — semua input tersimpan OTOMATIS
  (autosave debounce ~1,2 detik + indikator status).

## 3. Aturan Tampilan & Format Angka (disepakati)

1. Label variabel di Data Mentah memakai SINGKATAN:
   Pegawai Negeri Sipil => PNS,
   Pegawai Pemerintah dengan Perjanjian Kerja => PPPK,
   Aparatur Sipil Negara => ASN,
   Tenaga Kependidikan => Tendik.
   (Nama panjang disimpan di `label_panjang` untuk tooltip/laporan.)
2. Label analisis & satuan di Hasil memakai nama LENGKAP
   (contoh: "Mahasiswa per Sumber Daya Manusia").
3. Input angka format id-ID otomatis:
   ribuan pakai titik (1.000.000), desimal pakai koma.
4. Kolom Target SELALU boleh desimal (koma) untuk semua tipe,
   sampai 4 digit desimal. Contoh: 40,5 / 45,75 / 10.000,5.
   Koma yang baru diketik WAJIB dipertahankan di layar
   ("40," tetap "40,") agar bisa lanjut ketik desimalnya.
5. Hasil hitung: rasio 4 desimal, capaian 2 desimal + " %",
   currency "Rp ...", cacah/orang ribuan id-ID.

## 4. Aturan Hitung (disepakati)

1. Realisasi TAHUNAN rasio = rasio dari nilai TAHUNAN
   (semester 0) tiap variabel pembentuknya.
2. Prognosa TAHUNAN rasio = SAMA dengan Realisasi tahunan
   (dihitung dari nilai tahunan variabel pembentuk).
3. Prognosa variabel: S1+S2 jika keduanya ada; S1x2 jika baru S1;
   jika hanya tahunan => prognosa = realisasi tahunan.
4. Capaian = null jika target kosong/nol atau prognosa kosong.
5. Guard tahun LOCKED/CLOSED: hanya admin + alasan wajib,
   tercatat OVERRIDE di history.

## 5. Cara Menambah Analisis / Variabel Baru

1. Tambah 1 entri ke `RbaAnalisisRegistry::definitions()`
   (kode, jenis, KELOMPOK RASIO, nama lengkap, pembilang[], penyebut[],
   satuan lengkap, tipe: rasio|currency|cacah|orang|persen, desimal).
   Kelompok = nama rasio induk (contoh: "Rasio Output Layanan
   terhadap Sumber Daya Manusia"), dipakai untuk pengelompokan kartu.
2. Jika butuh variabel baru: tambah ke `variables()`
   (kode, label SINGKATAN, label_panjang, grup, tipe).
3. TIDAK perlu migrasi — tabel generik:
   `rba_analisis_input` (semester 0=tahunan,1,2),
   `rba_analisis_target`, `rba_analisis_variable_target`.
4. UI, validasi, autosave, history otomatis mengikuti registry.
5. Koreksi kode analisis (contoh ROLSM-Total => ROLSDM-Total):
   migrasi data lama + catat history alasan koreksi.

## 6. Data Awal (contoh pertama — produktivitas)

data total_mahasiswa
data total_sdm:
        Jumlah Dosen/Pengajar Pegawai ASN dan Non-ASN
        - jlh_dosen_pns (tampil: Jumlah Dosen PNS)
        - jlh_dosen_pppk (tampil: Jumlah Dosen PPPK)
        - jlh_dosen_non_asn (tampil: Jumlah Dosen Non-ASN)

        Jumlah Tenaga Kependidikan/Non-Pengajar Pegawai ASN dan Non-ASN
        - tendik_pns (tampil: Jumlah Tendik PNS)
        - tendik_pppk (tampil: Jumlah Tendik PPPK)
        - tendik_non_asn (tampil: Jumlah Tendik Non-ASN)

data PNBP BLU
    - Layanan Utama
    - Pengelolaan Kas
    - Optimalisasi aset tetap dan kerja sama
    - unit bisnis
    - hibah

data skor IKM
data Skor Maturity Rating


input target, realisasi dan menghasil prognosa

target diinput, realisasi dihitung dari data yang diinput

ANALISIS
1. jenis ANALISIS (produktivitas, efisiensi, inovasi dan kesesuaian)
        - analisis produktivitas
                - Rasio output layanan terhadap SDM
                        - ROLSDM-Total => total_mahasiswa/total_SDM
                        - ROLSDM-Dosen => total_mahasiswa/(jlh_dosen_pns + jlh_dosen_pppk + jlh_dosen_non_asn)
                        - ROLSDM-Tendik => total_mahasiswa/(tendik_pns + tendik_pppk + tendik_non_asn)
                - Rasio Pendapatan (PNBP) Terhadap SDM
                        - RPSDM-Total => total PNBP BLU/total_SDM
                        - RPSDM-Dosen => total PNBP BLU/Jumlah Dosen/Pengajar Pegawai ASN dan Non-ASN
                        - RPSDM-Tendik => total PNBP BLU/Jumlah Tenaga Kependidikan/Non-Pengajar Pegawai ASN dan Non-ASN
                - Rasio Pendapatan (PNBP) hasil Optimalisasi Asset
                        - RPOA => (Pengelolaan kas + Optimalisasi aset tetap dan kerja sama + unit bisnis)/total PNBP BLU
                - Peningkatan Jumlah Output (PJOL)
                        - PJOL-Mahasiswa => total_mahasiswa(tahun) - total_mahasiswa(tahun-1)
                        - PJOL-Pendapatan => total PNBP BLU(tahun) - total PNBP BLU(tahun-1)
                - Peningkatan Kualitas Layanan (PKL) hasil survey IKM
                        - PKL-IKM => skor IKM(tahun) - skor IKM(tahun-1)
                        - PKL-Maturity Rating => Skor Maturity Rating(tahun) - Skor Maturity Rating(tahun-1)
                - Target Output Layanan (TOL)
                        - TOL Mahasiswa => total_mahasiswa(tahun)/total_mahasiswa(tahun-1)
                        - TOL Pendapatan => total PNBP BLU(tahun) / total PNBP BLU(tahun-1)