# AGENTS.md — Fitur Tambahan: RBA (Rencana Bisnis Anggaran)

## 0. Konteks Dokumen

Dokumen ini adalah panduan bagi AI coding agent / developer agent untuk membangun fitur tambahan **RBA (Rencana Bisnis Anggaran)** pada aplikasi yang **sudah selesai dan sudah berjalan**.

Fitur RBA ini harus diperlakukan sebagai **modul tambahan yang terisolasi**. Agent dilarang mengubah, merefactoring, atau mengganggu alur fitur lama yang sudah stabil, kecuali ada perubahan yang sangat minimal, additive, dan disetujui secara eksplisit.

Prinsip utama:

> **Tambah fitur baru tanpa merusak flow lama.**
>
> **Semua perubahan harus additive, terisolasi, dapat di-roll back, dan tercatat dalam history.**

---

## 1. Misi Agent

Agent bertugas implementing fitur RBA dengan ketentuan:

1. Membangun modul RBA sebagai fitur tambahan.
2. Tidak mengubah business flow aplikasi existing.
3. Tidak memodifikasi tabel, API, halaman, service, controller, middleware, atau komponen shared yang sudah berjalan, kecuali benar-benar diperlukan dan bersifat additive.
4. Menggunakan pola, konvensi, auth, role, UI component, logger, error handler, dan history/audit system yang sudah ada di aplikasi.
5. Jika aplikasi existing belum memiliki history/audit system yang bisa dipakai, agent boleh membuat history log lokal khusus modul RBA tanpa mengubah sistem global.
6. Semua aktivitas insert, update, delete, dan admin override pada data RBA harus tercatat dalam history.
7. Fitur harus dapat dinonaktifkan melalui feature flag tanpa memengaruhi aplikasi lama.

---

## 2. Non-Negotiable Guardrails

Agent **dilarang keras** melakukan hal berikut tanpa persetujuan manusia:

### 2.1. Dilarang mengubah flow lama

- Jangan mengubah controller, service, model, route, page, component, hook, store, atau utility milik fitur existing.
- Jangan mengubah alur login, session, authorization, tenant/company resolution, approval lama, posting lama, jurnal lama, laporan lama, atau proses bisnis lain yang sudah berjalan.
- Jangan mengubah response format global kecuali modul RBA memang harus mengikuti format global yang sudah ada.
- Jangan mengubah middleware global.
- Jangan mengubah error handler global.
- Jangan mengubah logger global.
- Jangan mengubah konfigurasi database global.
- Jangan mengubah authentication/authorization core.

### 2.2. Dilarang melakukan breaking change

- Jangan menghapus tabel existing.
- Jangan rename kolom existing.
- Jangan mengubah tipe data kolom existing.
- Jangan mengubah constraint existing.
- Jangan mengubah API contract existing.
- Jangan mengubah menu existing secara destruktif.
- Jangan mengubah permission existing secara destruktif.
- Jangan mengubah seeded data existing.
- Jangan mengubah migration yang sudah dijalankan di production.

### 2.3. Dilarang melakukan refactor luas

- Jangan merapikan kode di luar modul RBA.
- Jangan meng-upgrade dependency kecuali benar-benar diperlukan dan disetujui.
- Jangan mengubah build tool, bundler, linter, formatter, CI/CD, Docker, atau deployment pipeline kecuali disetujui.
- Jangan mengubah struktur folder global.
- Jangan memindahkan file existing.

### 2.4. Dilarang menyentuh shared file kecuali whitelist

Agent hanya boleh menyentuh file shared jika termasuk kategori berikut:

1. Registrasi route baru untuk modul RBA.
2. Registrasi menu baru untuk modul RBA.
3. Penambahan permission baru untuk modul RBA.
4. Penambahan feature flag baru.
5. Penambahan konfigurasi environment variable baru dengan default aman.
6. Integrasi ke history/audit system existing melalui adapter, tanpa mengubah core history system.

Jika agent merasa perlu mengubah file di luar whitelist tersebut, agent **harus berhenti dan meminta konfirmasi manusia**.

### 2.5. Dilarang menjalankan migrasi database

- Jangan menjalankan `php artisan migrate`, `migrate:fresh`, `migrate:refresh`, `migrate:rollback`, `db:seed`, atau perintah sejenis apa pun tanpa persetujuan manusia.
- Perubahan skema hanya boleh disiapkan sebagai file migrasi baru — **tidak dieksekusi** oleh agent.
- Jangan menghapus baris dari tabel `migrations` secara manual.
- Verifikasi cukup dengan `migrate:status` (read-only) atau `Schema::hasTable()` via tinker.
- Alasan: eksekusi/refresh migrasi pernah menghilangkan tabel `rba_*` di database lokal padahal catatan migrasi berstatus Ran.

---

## 3. Scope Fitur RBA

### 3.1. In Scope

Modul RBA tahap ini mencakup:

1. Master data:
   - Jenis RBA
   - Kategori RBA
   - Indikator RBA
   - Detail RBA

2. Referensi tahun:
   - Tahun anggaran
   - Status tahun: OPEN, LOCKED, CLOSED
   - Tahun berjalan / current year

3. Transaksi target:
   - Target per detail per tahun
   - Penyusunan target tahun proyeksi
   - Tampilan target multi-year

4. Transaksi realisasi:
   - Realisasi per detail per tahun per semester
   - Semester 1 dan Semester 2
   - Perhitungan persentase pencapaian sederhana

5. Laporan / MIS sederhana:
   - Laporan target multi-year
   - Laporan realisasi per semester
   - Laporan persentase pencapaian
   - Rekap per jenis, kategori, indikator, dan detail
   - History perubahan data

6. Kontrol akses:
   - User biasa tidak dapat mengubah data locked.
   - Admin dapat melakukan override terhadap data locked dengan alasan.
   - Semua override tercatat di history.

### 3.2. Out of Scope

Tahap ini **tidak** mencakup:

1. Multi-currency.
2. Satuan pengukuran selain Rupiah.
3. Tipe nilai selain moneter.
4. Metode agregasi selain SUM sederhana.
5. Forecasting kompleks.
6. Weighted average.
7. Rasio keuangan khusus, misalnya ROA, ROI, margin %, kecuali nanti diminta terpisah.
8. Integrasi GL otomatis, kecuali aplikasi existing sudah menyediakan mekanisme import/export yang bisa dipakai tanpa mengubah core.
9. Workflow approval berlapis yang rumit, kecuali menggunakan workflow existing.
10. Perubahan pada modul aplikasi lama.

---

## 4. Aturan Integrasi dengan Aplikasi Existing

Agent wajib melakukan discovery terlebih dahulu sebelum menulis kode.

### 4.1. Discovery yang harus dilakukan

Agent harus memeriksa:

1. Framework backend yang digunakan.
2. Framework frontend yang digunakan.
3. ORM / query builder / database migration tool.
4. Pola penamaan tabel, kolom, file, route, dan component.
5. Sistem authentication dan authorization.
6. Sistem role dan permission.
7. Sistem menu / navigation.
8. Sistem history / audit log, jika ada.
9. Sistem error handling.
10. Sistem logging.
11. Sistem response API standar.
12. Sistem pagination.
13. Sistem export laporan, jika ada.
14. Apakah aplikasi bersifat multi-tenant, multi-company, multi-branch, atau single entity.
15. Apakah ada fiscal year / periode akuntansi existing yang harus diikuti.

### 4.2. Follow existing convention

Agent harus mengikuti konvensi aplikasi existing, misalnya:

- Jika existing menggunakan `snake_case` untuk database, ikuti.
- Jika existing menggunakan `camelCase` untuk API response, ikuti.
- Jika existing menggunakan REST, ikuti.
- Jika existing menggunakan GraphQL, ikuti.
- Jika existing menggunakan layering Controller → Service → Repository, ikuti.
- Jika existing menggunakan Clean Architecture / Hexagonal, ikuti pola modul yang sudah ada.
- Jika existing menggunakan component library tertentu, gunakan component tersebut.
- Jangan memperkenalkan pola arsitektur baru hanya untuk modul RBA.

### 4.3. Modularitas

Fitur RBA harus ditempatkan dalam modul terpisah.

Contoh struktur yang disarankan, sesuaikan dengan framework existing:

```text
src/
  modules/
    rba/
      controllers/
      services/
      repositories/
      dtos/
      validators/
      entities/
      migrations/
      seeds/
      tests/
      routes.ts
      index.ts