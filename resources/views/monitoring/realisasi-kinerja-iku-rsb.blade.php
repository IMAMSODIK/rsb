@extends('layouts.template')

@section('content')
    <style>
        #realisasiTable { min-width: 2400px; }
        #realisasiTable th { text-align: center; vertical-align: middle; white-space: nowrap; }
        #realisasiTable td.uraian-cell { min-width: 280px; width: 280px; }
        #realisasiTable td.keterangan-cell { min-width: 240px; width: 240px; }
        #realisasiTable td.dokumen-cell { min-width: 260px; width: 260px; }
        #realisasiTable td.numeric-cell { text-align: right; white-space: nowrap; }
        #realisasiTable .realization-input { text-align: left; }
        #realisasiTable td.target-cell { white-space: nowrap; }
    </style>

    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item text-secondary">Monitoring</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Realisasi Kinerja IKU RSB</li>
        </ol>
    </div>

    <div class="app-body"><div class="col-12"><div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Realisasi Kinerja IKU RSB</h5>
            <div class="d-flex align-items-center gap-2">
                @if ($isAdmin)
                    <label for="unit_kerja_id" class="mb-0 text-nowrap">Unit Kerja</label>
                    <select id="unit_kerja_id" class="form-select form-select-sm" style="min-width: 180px">
                        <option value="">Semua Unit Kerja</option>
                        @foreach ($unitKerjaList as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->kode_unit }} - {{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                @endif
                <label for="periode_id" class="mb-0 text-nowrap">Periode</label>
                <select id="periode_id" class="form-select form-select-sm" style="min-width: 180px">
                    <option value="">-- Pilih Periode --</option>
                    @foreach ($periode as $item)
                        <option value="{{ $item->id }}" @selected($periodeAktif?->id === $item->id)>{{ $item->tahun_awal }} - {{ $item->tahun_akhir }}{{ $item->is_active ? ' (Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body">
            <div id="unitInfo" class="alert alert-info d-none"></div>
            <div id="tableMessage" class="text-secondary">Pilih periode untuk memuat realisasi IKU.</div>
            <div id="tableWrapper" class="table-responsive d-none"><table class="table table-bordered table-hover align-middle" id="realisasiTable">
                <thead><tr id="headerTop"><th rowspan="2" width="50">No</th><th rowspan="2">Kode IKU</th><th rowspan="2">Tujuan RSB</th><th rowspan="2">Sasaran Program</th><th rowspan="2">Sasaran Strategis</th><th rowspan="2">Uraian</th></tr><tr id="headerBottom"></tr></thead>
                <tbody></tbody>
            </table></div>
        </div>
    </div></div></div>
@endsection

@push('scripts')
<script>
    $(function () {
        const DATA_URL = '{{ route("monitoring.realisasi-kinerja-iku-rsb.data") }}';
        const DRAFT_URL = '{{ route("monitoring.realisasi-kinerja-iku-rsb.draft") }}';
        const DOCUMENT_URL = '{{ route("monitoring.realisasi-kinerja-iku-rsb.documents.store") }}';
        const DOCUMENT_DELETE_URL = '{{ url("monitoring/realisasi-kinerja-iku-rsb/documents") }}';
        const ACTIVE_PERIOD_ID = '{{ $periodeAktif?->id ?? '' }}';
        const IS_ADMIN = @json($isAdmin);
        let responseYears = [];
        const saveTimers = {};

         function message(text) { $('#tableMessage').removeClass('d-none').text(text); $('#tableWrapper').addClass('d-none'); }
         function destroyDataTable() { if ($.fn.DataTable.isDataTable('#realisasiTable')) $('#realisasiTable').DataTable().destroy(); }
         function initDataTable() {
             $('#realisasiTable').DataTable({
                 order: [],
                 scrollX: true,
                 language: {
                     search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                     info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                     infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
                     emptyTable: 'Belum ada data realisasi kinerja IKU RSB',
                     paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                 },
             });
         }
        function docs($box, list) {
            $box.empty();
            if (!list || !list.length) return $('<span>', { class: 'text-muted small', text: 'Belum ada' }).appendTo($box);
            list.forEach(doc => {
                const $item = $('<div>', { class: 'd-flex align-items-center gap-1 mb-1' });
                $('<a>', { href: doc.url, target: '_blank', class: 'small text-truncate', text: doc.nama_asli }).appendTo($item);
                if (!IS_ADMIN) {
                    $('<button>', { type: 'button', class: 'btn btn-sm btn-link text-danger p-0 delete-document', 'data-id': doc.id, html: '<i class="bi bi-x-circle"></i>' }).appendTo($item);
                }
                $box.append($item);
            });
        }
        function percentage($row) {
             const target = parseFloat($row.find('.target-value').data('target'));
             const s1Value = $row.find('[data-semester="1"]').val().trim();
             const s2Value = $row.find('[data-semester="2"]').val().trim();
             const s1 = s1Value === '' ? 0 : parseFloat(s1Value);
             const s2 = s2Value === '' ? 0 : parseFloat(s2Value);
             const value = Number.isFinite(target) && Number.isFinite(s1) && Number.isFinite(s2) && target > 0
                 ? ((s1 + s2) / target) * 100
                 : null;
            $row.find('.percentage-value').text(value == null ? '-' : `${value.toFixed(2)}%`);
        }
         function render(response) {
             destroyDataTable();
             responseYears = response.years;
            $('#unitInfo').removeClass('d-none').text(`Unit kerja: ${response.unit_kerja.kode_unit} - ${response.unit_kerja.nama_unit} | Persentase dihitung dari target tahunan`);
            $('#tableMessage').addClass('d-none'); $('#tableWrapper').removeClass('d-none');
            const $top = $('#headerTop').empty();
             $('<th>', { rowspan: 2, width: 50, text: 'No' }).appendTo($top);
             $('<th>', { rowspan: 2, text: 'Kode IKU' }).appendTo($top);
             $('<th>', { rowspan: 2, text: 'Tujuan RSB' }).appendTo($top);
             $('<th>', { rowspan: 2, text: 'Sasaran Program' }).appendTo($top);
             $('<th>', { rowspan: 2, text: 'Sasaran Strategis' }).appendTo($top);
            $('<th>', { rowspan: 2, text: 'Uraian' }).appendTo($top);
            response.years.forEach(year => $('<th>', { colspan: 8, class: 'text-center', text: year }).appendTo($top));
            $('<th>', { rowspan: 2, width: 100, text: 'Status' }).appendTo($top);
            const $bottom = $('#headerBottom').empty();
            response.years.forEach(() => ['Target', 'Realisasi S1', 'Keterangan S1', 'Dokumen S1', 'Realisasi S2', 'Keterangan S2', 'Dokumen S2', 'Pencapaian'].forEach(label => $('<th>', { text: label }).appendTo($bottom)));
            const $body = $('#realisasiTable tbody').empty();
            if (!response.data.length) return message('Belum ada target kinerja IKU untuk unit kerja dan periode ini.');
            response.data.forEach((item, index) => {
                const $row = $('<tr>').attr({ 'data-target-id': item.id });
                 $('<td>', { text: index + 1 }).appendTo($row); $('<td>', { text: item.kode_iku }).appendTo($row);
                 $('<td>', { text: item.tujuan_rsb || '-' }).appendTo($row); $('<td>', { text: item.sasaran_program || '-' }).appendTo($row);
                 $('<td>', { text: item.sasaran_strategis || '-' }).appendTo($row); $('<td>', { text: item.uraian, class: 'uraian-cell' }).appendTo($row);
                response.years.forEach(year => {
                    const target = item.target_tahunan?.[year] ?? ''; const annual = item.realisasi_tahunan?.[year] || {};
                    $('<td>', { class: 'target-cell' }).append($('<span>', { class: 'badge bg-light text-dark target-value', text: target === '' ? '-' : target, 'data-target-year': year, 'data-target': target })).appendTo($row);
                    [[1, annual.semester_1 || {}], [2, annual.semester_2 || {}]].forEach(([semester, value]) => {
                         $('<td>', { class: 'numeric-cell' }).append($('<input>', { type: 'text', class: 'form-control form-control-sm realization-input', 'data-year': year, 'data-semester': semester, value: value.nilai ?? '', disabled: IS_ADMIN })).appendTo($row);
                        $('<td>', { class: 'keterangan-cell' }).append($('<textarea>', { class: 'form-control form-control-sm semester-note', rows: 2, 'data-year': year, 'data-semester': semester, text: value.keterangan || '', disabled: IS_ADMIN })).appendTo($row);
                        const $box = $('<div>', { class: 'document-list mb-2' }); docs($box, (item.dokumen || {})[`${year}_${semester}`] || []);
                        const $documentCell = $('<td>', { class: 'dokumen-cell' }).append($box);
                        if (!IS_ADMIN) {
                            $documentCell.append($('<input>', { type: 'file', multiple: true, class: 'form-control form-control-sm document-input', accept: '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png', 'data-year': year, 'data-semester': semester })).append($('<button>', { type: 'button', class: 'btn btn-sm btn-outline-primary mt-1 upload-documents', 'data-year': year, 'data-semester': semester, text: 'Upload' }));
                        }
                        $documentCell.appendTo($row);
                    });
                    $('<td>', { class: 'text-nowrap numeric-cell' }).append($('<span>', { class: 'percentage-value', 'data-percentage-year': year, text: annual.persentase == null ? '-' : `${Number(annual.persentase).toFixed(2)}%` })).appendTo($row);
                });
                 $('<td>').append($('<span>', { class: 'save-status text-muted small', text: item.status === 'draft' && Object.keys(item.realisasi_tahunan || {}).length ? 'Draft tersimpan' : 'Belum disimpan' })).appendTo($row); $body.append($row);
             });
             initDataTable();
         }
        function load(period) {
            if (!period) return message('Pilih periode untuk memuat realisasi IKU.');
            $('#tableMessage').removeClass('d-none').text('Memuat data...');
            const params = new URLSearchParams({ periode_id: period });
            const unitKerjaId = $('#unit_kerja_id').val();
            if (unitKerjaId) params.append('unit_kerja_id', unitKerjaId);
            fetch(`${DATA_URL}?${params.toString()}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(r => { if (r.success) { render(r); } else message(r.message || 'Gagal memuat data.'); }).catch(() => message('Gagal memuat realisasi kinerja IKU RSB.'));
        }
        function save($row) {
            const annual = {}; const targetId = $row.data('target-id');
            responseYears.forEach(year => {
                annual[year] = { semester_1: {}, semester_2: {} };
                $('#realisasiTable tbody tr[data-target-id="' + targetId + '"]').find(`.realization-input[data-year="${year}"]`).each(function () { annual[year][`semester_${$(this).data('semester')}`].nilai = $(this).val(); });
                $('#realisasiTable tbody tr[data-target-id="' + targetId + '"]').find(`.semester-note[data-year="${year}"]`).each(function () { annual[year][`semester_${$(this).data('semester')}`].keterangan = $(this).val(); });
            });
            const $status = $row.find('.save-status').text('Menyimpan...');
            fetch(DRAFT_URL, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }, body: JSON.stringify({ target_id: targetId, realisasi_tahunan: annual }) }).then(r => r.json()).then(r => { if (r.success) { $status.text('Draft tersimpan'); Object.entries(r.realisasi_tahunan || {}).forEach(([year, value]) => $row.find(`[data-percentage-year="${year}"]`).text(value.persentase == null ? '-' : `${Number(value.persentase).toFixed(2)}%`)); } else $status.text(r.message || 'Gagal menyimpan').addClass('text-danger'); }).catch(() => $status.text('Gagal menyimpan').addClass('text-danger'));
        }
        $('#periode_id').on('change', function () { load(this.value); });
        $('#unit_kerja_id').on('change', function () { load($('#periode_id').val()); });
        $('#realisasiTable').on('input', '.realization-input, .semester-note', function () { const $row = $(this).closest('tr'); if ($(this).hasClass('realization-input')) percentage($row); const id = $row.data('target-id'); clearTimeout(saveTimers[id]); $row.find('.save-status').text('Menunggu...'); saveTimers[id] = setTimeout(() => save($row), 700); });
        $('#realisasiTable').on('click', '.upload-documents', function () { const $row = $(this).closest('tr'); const year = $(this).data('year'); const semester = $(this).data('semester'); const input = $row.find(`.document-input[data-year="${year}"][data-semester="${semester}"]`)[0]; if (!input.files.length) return showAlert('warning', 'Pilih dokumen terlebih dahulu.'); const fd = new FormData(); fd.append('target_id', $row.data('target-id')); fd.append('tahun', year); fd.append('semester', semester); Array.from(input.files).forEach(file => fd.append('dokumen[]', file)); fetch(DOCUMENT_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }, body: fd }).then(r => r.json()).then(r => r.success ? (showAlert('success', r.message), load($('#periode_id').val())) : showAlert('error', r.message)).catch(() => showAlert('error', 'Dokumen gagal diunggah.')); });
        $('#realisasiTable').on('click', '.delete-document', function () { fetch(`${DOCUMENT_DELETE_URL}/${$(this).data('id')}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' } }).then(r => r.json()).then(r => r.success ? (showAlert('success', r.message), load($('#periode_id').val())) : showAlert('error', r.message)).catch(() => showAlert('error', 'Dokumen gagal dihapus.')); });
        if (ACTIVE_PERIOD_ID) load(ACTIVE_PERIOD_ID);
    });
</script>
@endpush
