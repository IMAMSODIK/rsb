@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">RBA</li>
            <li class="breadcrumb-item text-secondary">Laporan</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">History RBA</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">History Perubahan RBA</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <label for="filter_tabel" class="visually-hidden">Filter tabel</label>
                        <select id="filter_tabel" class="form-select form-select-sm" title="Filter berdasarkan tabel">
                            <option value="">-- Semua Tabel --</option>
                            <option value="rba_jenis">rba_jenis</option>
                            <option value="rba_kategori">rba_kategori</option>
                            <option value="rba_indikator">rba_indikator</option>
                            <option value="rba_detail">rba_detail</option>
                            <option value="rba_tahun_anggaran">rba_tahun_anggaran</option>
                            <option value="rba_target">rba_target</option>
                            <option value="rba_realisasi">rba_realisasi</option>
                        </select>
                        <label for="filter_aksi" class="visually-hidden">Filter aksi</label>
                        <select id="filter_aksi" class="form-select form-select-sm" title="Filter berdasarkan aksi">
                            <option value="">-- Semua Aksi --</option>
                            <option value="INSERT">INSERT</option>
                            <option value="UPDATE">UPDATE</option>
                            <option value="DELETE">DELETE</option>
                            <option value="OVERRIDE">OVERRIDE</option>
                        </select>
                        <a href="{{ route('rba.laporan.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Laporan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                    <th>Tabel</th>
                                    <th>ID Ref</th>
                                    <th>User</th>
                                    <th>Alasan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        const BASE = '{{ route("rba.laporan.history.data") }}';
        let table;

        function badge(aksi) {
            const map = { INSERT: 'bg-success', UPDATE: 'bg-primary', DELETE: 'bg-danger', OVERRIDE: 'bg-warning text-dark' };
            return `<span class="badge ${map[aksi] || 'bg-secondary'}">${aksi}</span>`;
        }

        table = $('#datatable').DataTable({
            ajax: {
                url: BASE,
                data: d => {
                    d.tabel_referensi = $('#filter_tabel').val() || undefined;
                    d.aksi = $('#filter_aksi').val() || undefined;
                },
                dataSrc: 'data',
            },
            order: [[1, 'desc']],
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'created_at' },
                { data: 'aksi', render: badge },
                { data: 'tabel_referensi' },
                { data: 'referensi_id' },
                { data: 'user.name', defaultContent: '-' },
                { data: 'alasan', defaultContent: '-', render: d => d || '-' },
            ],
            language: {
                search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada history RBA',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        $('#filter_tabel, #filter_aksi').on('change', () => table.ajax.reload(null, false));
    });
</script>
@endpush
