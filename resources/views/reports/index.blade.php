@extends('layouts.template')

@section('content')
    <style>
        #reportTable { min-width: 2200px; }
        #reportTable th { text-align: center; vertical-align: middle; white-space: nowrap; }
        #reportTable td.uraian-cell { min-width: 280px; }
        #reportTable td.keterangan-cell { min-width: 220px; }
        #reportTable td.dokumen-cell { min-width: 220px; }
        #reportTable td.numeric-cell { text-align: right; white-space: nowrap; }
        .report-export-btn { min-width: 110px; padding-left: 16px; padding-right: 16px; }
    </style>
    <div class="app-hero-header d-flex align-items-center"><ol class="breadcrumb">
        <li class="breadcrumb-item"><i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item text-secondary">Report</li><li class="breadcrumb-item text-secondary" aria-current="page">{{ $title }}</li>
    </ol></div>
    <div class="app-body"><div class="col-12"><div class="card">
        <div class="card-header d-flex justify-content-between align-items-center"><h5 class="card-title mb-0">{{ $title }}</h5>
            <div class="d-flex gap-2 align-items-center"><select id="periode_id" class="form-select form-select-sm"><option value="">-- Periode --</option>@foreach($periode as $item)<option value="{{ $item->id }}" @selected($periodeAktif?->id === $item->id)>{{ $item->tahun_awal }} - {{ $item->tahun_akhir }}{{ $item->is_active ? ' (Aktif)' : '' }}</option>@endforeach</select>
                <select id="mode" class="form-select form-select-sm"><option value="tahunan">Tahunan</option><option value="semester_1">Semester 1</option><option value="semester_2">Semester 2</option></select>
                <button id="btnExcel" class="btn btn-sm btn-success report-export-btn"><i class="bi bi-file-earmark-excel"></i> Excel</button>
            </div>
        </div>
        <div class="card-body"><div id="reportInfo" class="alert alert-info d-none"></div><div id="reportMessage" class="text-secondary">Pilih periode untuk memuat laporan.</div>
            <div id="reportWrapper" class="table-responsive d-none"><table class="table table-bordered table-hover align-middle" id="reportTable"><thead><tr id="reportTop"><th rowspan="2">No</th><th rowspan="2">{{ $type === 'iku' ? 'Kode IKU' : 'Kode Kategori' }}</th>@if($type === 'iku')<th rowspan="2">Tujuan RSB</th><th rowspan="2">Sasaran Program</th><th rowspan="2">Sasaran Strategis</th>@else<th rowspan="2">Unit Kerja</th><th rowspan="2">Jenis Kinerja Layanan</th>@endif<th rowspan="2">Uraian</th></tr><tr id="reportBottom"></tr></thead><tbody></tbody></table></div>
        </div>
    </div></div></div>
@endsection

@push('scripts')
<script>
    $(function () {
        const DATA_URL = '{{ route($type === "layanan" ? "monitoring.laporan-capaian-kinerja-layanan.data" : "monitoring.laporan-capaian-kinerja-iku.data") }}';
        const EXPORT_URL = '{{ route($type === "layanan" ? "monitoring.laporan-capaian-kinerja-layanan.export" : "monitoring.laporan-capaian-kinerja-iku.export", ["format" => "excel"]) }}'.replace('/excel', '');
        const ACTIVE_PERIOD_ID = '{{ $periodeAktif?->id ?? '' }}';
        function docs(list) { return (list || []).map(doc => `<a href="${doc.url}" target="_blank" class="d-block small">${$('<div>').text(doc.nama_asli).html()}</a>`).join('') || '<span class="text-muted small">-</span>'; }
        function destroyDataTable() { if ($.fn.DataTable.isDataTable('#reportTable')) $('#reportTable').DataTable().destroy(); }
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
        function render(response) {
            destroyDataTable();
            $('#reportInfo').removeClass('d-none').text(`Unit kerja: ${response.unit_kerja} | Periode: ${response.periode}`); $('#reportMessage').addClass('d-none'); $('#reportWrapper').removeClass('d-none');
             const $top = $('#reportTop').empty(); $('<th>', { rowspan: 2, text: 'No' }).appendTo($top); $('<th>', { rowspan: 2, text: '{{ $type === "iku" ? "Kode IKU" : "Kode Kategori" }}' }).appendTo($top); @if($type === 'iku') $('<th>', { rowspan: 2, text: 'Tujuan RSB' }).appendTo($top); $('<th>', { rowspan: 2, text: 'Sasaran Program' }).appendTo($top); $('<th>', { rowspan: 2, text: 'Sasaran Strategis' }).appendTo($top); @else $('<th>', { rowspan: 2, text: 'Unit Kerja' }).appendTo($top); $('<th>', { rowspan: 2, text: 'Jenis Kinerja Layanan' }).appendTo($top); @endif $('<th>', { rowspan: 2, text: 'Uraian' }).appendTo($top);
            const mode = response.mode;
            const showSemester1 = mode !== 'semester_2';
            const showSemester2 = mode !== 'semester_1';
            const yearColumnCount = 1 + (showSemester1 ? 3 : 0) + (showSemester2 ? 3 : 0) + 1;
            response.rows[0] && Object.keys(response.rows[0].detail).forEach(year => $('<th>', { colspan: yearColumnCount, class: 'text-center', text: year }).appendTo($top)); $('<th>', { rowspan: 2, text: 'Status' }).appendTo($top);
            const $bottom = $('#reportBottom').empty();
            response.rows[0] && Object.keys(response.rows[0].detail).forEach(() => {
                $('<th>', { text: 'Target' }).appendTo($bottom);
                if (showSemester1) ['Realisasi Semester 1', 'Keterangan Semester 1', 'Dokumen Semester 1'].forEach(label => $('<th>', { text: label }).appendTo($bottom));
                if (showSemester2) ['Realisasi Semester 2', 'Keterangan Semester 2', 'Dokumen Semester 2'].forEach(label => $('<th>', { text: label }).appendTo($bottom));
                $('<th>', { text: 'Pencapaian' }).appendTo($bottom);
            });
             const $body = $('#reportTable tbody').empty(); response.rows.forEach((row, index) => { const $tr = $('<tr>'); $('<td>', { text: index + 1 }).appendTo($tr); $('<td>', { text: row.kode }).appendTo($tr); @if($type === 'iku') $('<td>', { text: row.tujuan_rsb || '-' }).appendTo($tr); $('<td>', { text: row.sasaran_program || '-' }).appendTo($tr); $('<td>', { text: row.sasaran_strategis || '-' }).appendTo($tr); @else $('<td>', { text: row.unit_kerja || '-' }).appendTo($tr); $('<td>', { text: row.jenis_kinerja_layanan || '-' }).appendTo($tr); @endif $('<td>', { text: row.uraian, class: 'uraian-cell' }).appendTo($tr); Object.entries(row.detail).forEach(([year, detail]) => { $('<td>', { class: 'numeric-cell', text: detail.target ?? '-' }).appendTo($tr); if (showSemester1) { $('<td>', { class: 'numeric-cell', text: detail.semester_1.nilai ?? '-' }).appendTo($tr); $('<td>', { class: 'keterangan-cell', text: detail.semester_1.keterangan || '-' }).appendTo($tr); $('<td>', { class: 'dokumen-cell' }).html(docs(detail.dokumen?.[`${year}_1`])).appendTo($tr); } if (showSemester2) { $('<td>', { class: 'numeric-cell', text: detail.semester_2.nilai ?? '-' }).appendTo($tr); $('<td>', { class: 'keterangan-cell', text: detail.semester_2.keterangan || '-' }).appendTo($tr); $('<td>', { class: 'dokumen-cell' }).html(docs(detail.dokumen?.[`${year}_2`])).appendTo($tr); } $('<td>', { class: 'numeric-cell', text: detail.persentase == null ? '-' : `${detail.persentase}%` }).appendTo($tr); }); $('<td>', { text: 'Readonly' }).appendTo($tr); $body.append($tr); });
             initDataTable();
         }
        function load() { const period = $('#periode_id').val(); if (!period) return; $('#reportMessage').removeClass('d-none').text('Memuat laporan...'); fetch(`${DATA_URL}?periode_id=${period}&mode=${$('#mode').val()}`, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).then(r => r.success ? render(r) : $('#reportMessage').text(r.message || 'Gagal memuat laporan.')).catch(() => $('#reportMessage').text('Gagal memuat laporan.')); }
        function exportReport() { const period = $('#periode_id').val(); if (!period) return showAlert('warning', 'Pilih periode terlebih dahulu.'); window.location.href = `${EXPORT_URL}?periode_id=${period}&mode=${$('#mode').val()}`; }
        $('#periode_id, #mode').on('change', load); $('#btnExcel').on('click', exportReport); if (ACTIVE_PERIOD_ID) load();
    });
</script>
@endpush
