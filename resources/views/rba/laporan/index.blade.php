@extends('layouts.template')

@section('content')
    <style>
        #reportTable { min-width: 2200px; }
        #reportTable th { text-align: center; vertical-align: middle; white-space: nowrap; }
        #reportTable td.uraian-cell { min-width: 280px; }
        #reportTable td.numeric-cell { text-align: right; white-space: nowrap; min-width: 168px; }
        .report-export-btn { min-width: 110px; padding-left: 16px; padding-right: 16px; }
    </style>
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">RBA</li>
            <li class="breadcrumb-item text-secondary">Laporan</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Laporan RBA</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Laporan RBA (Rupiah)</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <label for="tahun_anggaran_id" class="visually-hidden">Filter tahun</label>
                        <select id="tahun_anggaran_id" class="form-select form-select-sm" title="Pilih tahun anggaran">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach ($tahun as $item)
                                <option value="{{ $item->id }}" @selected($current?->id === $item->id)>
                                    {{ $item->tahun }} ({{ $item->status }})
                                </option>
                            @endforeach
                        </select>
                        <label for="mode" class="visually-hidden">Mode laporan</label>
                        <select id="mode" class="form-select form-select-sm" title="Pilih mode laporan">
                            <option value="tahunan">Tahunan</option>
                            <option value="semester_1">Semester 1</option>
                            <option value="semester_2">Semester 2</option>
                            <option value="target">Target Saja</option>
                        </select>
                        <a href="{{ route('rba.laporan.history') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-clock-history"></i> History
                        </a>
                        <button id="btnExcel" class="btn btn-sm btn-success report-export-btn">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="reportInfo" class="alert alert-info d-none"></div>
                    <div id="rekapInfo" class="alert alert-success d-none"></div>
                    <div id="reportMessage" class="text-secondary">Pilih tahun untuk memuat laporan.</div>
                    <div id="reportWrapper" class="table-responsive d-none">
                        <table class="table table-bordered table-hover align-middle" id="reportTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Jenis</th>
                                    <th>Kategori</th>
                                    <th>Indikator</th>
                                    <th>Kode Detail</th>
                                    <th>Uraian</th>
                                    <th>Target (Rp)</th>
                                    <th>Realisasi S1 (Rp)</th>
                                    <th>Realisasi S2 (Rp)</th>
                                    <th>Total (Rp)</th>
                                    <th>Pencapaian</th>
                                    <th>Keterangan</th>
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
        const DATA_URL = '{{ route("rba.laporan.data") }}';
        const EXPORT_BASE = '{{ route("rba.laporan.export") }}';
        const ACTIVE_YEAR_ID = '{{ $current?->id ?? '' }}';

        function destroyDataTable() {
            if ($.fn.DataTable.isDataTable('#reportTable')) {
                $('#reportTable').DataTable().destroy();
            }
        }

        function initDataTable() {
            $('#reportTable').DataTable({
                order: [],
                scrollX: true,
                language: {
                    search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data laporan',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
            });
        }

        function cell(value) {
            return value == null ? '-' : value;
        }

        // Tampilan Rupiah: 5000000.5 -> "Rp 5.000.000,50" (desimal hanya jika ada).
        function formatRupiah(value) {
            if (value == null || value === '') return '-';
            const num = Number(String(value).replace(/[^0-9.,-]/g, '').replace(/\.(?=.*\.)/g, '').replace(',', '.'));
            if (isNaN(num)) return '-';
            return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        function render(response) {
            destroyDataTable();
            $('#reportInfo').removeClass('d-none').text(
                `Tahun: ${response.tahun.tahun} | Status: ${response.tahun.status} | Mode: ${response.mode}`
            );
            const rekap = response.rekap;
            $('#rekapInfo').removeClass('d-none').text(
                `Rekap SUM — Target: ${formatRupiah(rekap.target)} | Realisasi: ${formatRupiah(rekap.realisasi)} | Pencapaian: ${rekap.persentase == null ? '-' : rekap.persentase + '%'}`
            );
            $('#reportMessage').addClass('d-none');
            $('#reportWrapper').removeClass('d-none');

            const $body = $('#reportTable tbody').empty();
            response.rows.forEach((row, index) => {
                const $tr = $('<tr>');
                $('<td>', { text: index + 1 }).appendTo($tr);
                $('<td>', { text: (row.kode_jenis || '-') + ' - ' + (row.jenis || '-') }).appendTo($tr);
                $('<td>', { text: (row.kode_kategori || '-') + ' - ' + (row.kategori || '-') }).appendTo($tr);
                $('<td>', { text: (row.kode_indikator || '-') + ' - ' + (row.indikator || '-') }).appendTo($tr);
                $('<td>', { text: row.kode_detail }).appendTo($tr);
                $('<td>', { text: row.uraian, class: 'uraian-cell' }).appendTo($tr);
                $('<td>', { class: 'numeric-cell', text: formatRupiah(row.target) }).appendTo($tr);
                $('<td>', { class: 'numeric-cell', text: formatRupiah(row.semester_1) }).appendTo($tr);
                $('<td>', { class: 'numeric-cell', text: formatRupiah(row.semester_2) }).appendTo($tr);
                $('<td>', { class: 'numeric-cell', text: formatRupiah(row.total) }).appendTo($tr);
                $('<td>', { class: 'numeric-cell', text: row.persentase == null ? '-' : `${row.persentase}%` }).appendTo($tr);
                $('<td>', { text: row.keterangan || '-' }).appendTo($tr);
                $body.append($tr);
            });
            initDataTable();
        }

        function load() {
            const year = $('#tahun_anggaran_id').val();
            if (!year) return;
            $('#reportMessage').removeClass('d-none').text('Memuat laporan...');
            fetch(`${DATA_URL}?tahun_anggaran_id=${year}&mode=${$('#mode').val()}`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(r => r.success ? render(r) : $('#reportMessage').text(r.message || 'Gagal memuat laporan.'))
                .catch(() => $('#reportMessage').text('Gagal memuat laporan.'));
        }

        function exportReport() {
            const year = $('#tahun_anggaran_id').val();
            if (!year) return showAlert('warning', 'Pilih tahun terlebih dahulu.');
            window.location.href = `${EXPORT_BASE}?tahun_anggaran_id=${year}&mode=${$('#mode').val()}`;
        }

        $('#tahun_anggaran_id, #mode').on('change', load);
        $('#btnExcel').on('click', exportReport);
        if (ACTIVE_YEAR_ID) load();
    });
</script>
@endpush
