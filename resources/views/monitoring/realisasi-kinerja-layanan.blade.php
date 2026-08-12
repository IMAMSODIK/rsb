@extends('layouts.template')

@section('content')
    <style>
        #realisasiTable {
            min-width: 2200px;
        }

        #realisasiTable th {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        #realisasiTable td.uraian-cell {
            min-width: 280px;
            width: 280px;
        }

        #realisasiTable td.keterangan-cell {
            min-width: 240px;
            width: 240px;
        }

        #realisasiTable td.dokumen-cell {
            min-width: 260px;
            width: 260px;
        }

        #realisasiTable td.numeric-cell {
            text-align: right;
            white-space: nowrap;
        }

        #realisasiTable .realization-input {
            text-align: right;
        }

        #realisasiTable td.target-cell {
            white-space: nowrap;
        }
    </style>

    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">Monitoring</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Realisasi Kinerja Layanan</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Realisasi Kinerja Layanan</h5>
                    <div class="d-flex align-items-center gap-2">
                        <label for="periode_id" class="mb-0 text-nowrap">Periode</label>
                        <select id="periode_id" class="form-select form-select-sm" style="min-width: 180px">
                            <option value="">-- Pilih Periode --</option>
                            @foreach ($periode as $item)
                                <option value="{{ $item->id }}" @selected($periodeAktif?->id === $item->id)>
                                    {{ $item->tahun_awal }} - {{ $item->tahun_akhir }}{{ $item->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div id="unitInfo" class="alert alert-info d-none"></div>
                    <div id="tableMessage" class="text-secondary">Pilih periode untuk memuat realisasi.</div>
                    <div id="tableWrapper" class="table-responsive d-none">
                        <table class="table table-bordered table-hover align-middle" id="realisasiTable">
                            <thead>
                                <tr id="headerTop">
                                    <th rowspan="2" width="50">No</th>
                                    <th rowspan="2">Kode Kategori</th>
                                    <th rowspan="2">Uraian</th>
                                </tr>
                                <tr id="headerBottom"></tr>
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
        const DATA_URL = '{{ route("monitoring.realisasi-kinerja-layanan.data") }}';
        const DRAFT_URL = '{{ route("monitoring.realisasi-kinerja-layanan.draft") }}';
        const DOCUMENT_URL = '{{ route("monitoring.realisasi-kinerja-layanan.documents.store") }}';
        const DOCUMENT_DELETE_URL = '{{ url("monitoring/realisasi-kinerja-layanan/documents") }}';
        const ACTIVE_PERIOD_ID = '{{ $periodeAktif?->id ?? '' }}';
        const IS_ADMIN = @json($isAdmin);
        const saveTimers = {};

        function showMessage(message) {
            $('#tableMessage').removeClass('d-none').text(message);
            $('#tableWrapper').addClass('d-none');
        }

        function renderDocuments($container, documents) {
            $container.empty();
            if (!documents || !documents.length) {
                $('<span>', { class: 'text-muted small', text: 'Belum ada' }).appendTo($container);
                return;
            }
            documents.forEach(document => {
                const $item = $('<div>', { class: 'd-flex align-items-center gap-1 mb-1' });
                $('<a>', { href: document.url, target: '_blank', class: 'small text-truncate', text: document.nama_asli }).appendTo($item);
                $('<button>', {
                    type: 'button', class: 'btn btn-sm btn-link text-danger p-0 delete-document',
                    'data-id': document.id, html: '<i class="bi bi-x-circle"></i>',
                }).appendTo($item);
                $container.append($item);
            });
        }

        function calculatePercentage($row, year) {
            const target = parseFloat($row.find(`[data-target-year="${year}"]`).data('target')) || 0;
            const semester1 = parseFloat($row.find(`[data-year="${year}"][data-semester="1"]`).val()) || 0;
            const semester2 = parseFloat($row.find(`[data-year="${year}"][data-semester="2"]`).val()) || 0;
            const percentage = target > 0 ? ((semester1 + semester2) / target) * 100 : null;
            $row.find(`[data-percentage-year="${year}"]`).text(percentage === null ? '-' : `${percentage.toFixed(2)}%`);
        }

        function renderTable(response) {
            responseYears = response.years;
            $('#unitInfo').removeClass('d-none').text(
                `Unit kerja: ${response.unit_kerja.kode_unit} - ${response.unit_kerja.nama_unit} | Persentase dihitung dari target tahunan`
            );
            $('#tableMessage').addClass('d-none');
            $('#tableWrapper').removeClass('d-none');

            const $top = $('#headerTop').empty();
            $('<th>', { rowspan: 2, width: 50, text: 'No' }).appendTo($top);
            $('<th>', { rowspan: 2, text: 'Kode Kategori' }).appendTo($top);
            $('<th>', { rowspan: 2, text: 'Uraian' }).appendTo($top);
            response.years.forEach(year => $('<th>', { colspan: 8, class: 'text-center', text: year }).appendTo($top));
            $('<th>', { rowspan: 2, width: 100, text: 'Status' }).appendTo($top);

            const $bottom = $('#headerBottom').empty();
            response.years.forEach(() => {
                $('<th>', { text: 'Target' }).appendTo($bottom);
                $('<th>', { text: 'Realisasi S1' }).appendTo($bottom);
                $('<th>', { text: 'Keterangan S1' }).appendTo($bottom);
                $('<th>', { text: 'Dokumen S1' }).appendTo($bottom);
                $('<th>', { text: 'Realisasi S2' }).appendTo($bottom);
                $('<th>', { text: 'Keterangan S2' }).appendTo($bottom);
                $('<th>', { text: 'Dokumen S2' }).appendTo($bottom);
                $('<th>', { text: 'Pencapaian' }).appendTo($bottom);
            });

            const $body = $('#realisasiTable tbody').empty();
            if (!response.data.length) {
                showMessage('Belum ada target kinerja layanan untuk unit kerja dan periode ini.');
                return;
            }

            response.data.forEach((item, index) => {
                const $row = $('<tr>').attr({ 'data-target-id': item.id });
                $('<td>', { text: index + 1 }).appendTo($row);
                $('<td>', { text: item.kode_kategori }).appendTo($row);
                $('<td>', { text: item.uraian, class: 'uraian-cell' }).appendTo($row);
                response.years.forEach(year => {
                    const target = item.target_tahunan?.[year] ?? '';
                    const annual = item.realisasi_tahunan?.[year] || {};
                    const semester1 = annual.semester_1 || {};
                    const semester2 = annual.semester_2 || {};
                    const documents = item.dokumen || {};
                    $('<td>', { class: 'target-cell' }).append($('<span>', {
                        class: 'badge bg-light text-dark target-value', text: target === '' ? '-' : target,
                        'data-target-year': year, 'data-target': target,
                    })).appendTo($row);
                    [[1, semester1], [2, semester2]].forEach(([semester, value]) => {
                        $('<td>', { class: 'numeric-cell' }).append($('<input>', {
                            type: 'number', min: 0, step: 'any', class: 'form-control form-control-sm realization-input',
                            'data-year': year, 'data-semester': semester, value: value.nilai ?? '',
                        })).appendTo($row);
                        $('<td>', { class: 'keterangan-cell' }).append($('<textarea>', {
                            class: 'form-control form-control-sm semester-note', rows: 2,
                            'data-year': year, 'data-semester': semester, text: value.keterangan || '',
                        })).appendTo($row);
                        const $documents = $('<div>', { class: 'document-list mb-2' });
                        renderDocuments($documents, documents[`${year}_${semester}`] || []);
                        $('<td>', { class: 'dokumen-cell' }).append($documents).append($('<input>', {
                            type: 'file', multiple: true, class: 'form-control form-control-sm document-input',
                            accept: '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png', 'data-year': year, 'data-semester': semester,
                        })).append($('<button>', {
                            type: 'button', class: 'btn btn-sm btn-outline-primary mt-1 upload-documents',
                            'data-year': year, 'data-semester': semester, text: 'Upload',
                        })).appendTo($row);
                    });
                    $('<td>', { class: 'text-nowrap numeric-cell' }).append($('<span>', {
                        class: 'percentage-value', 'data-percentage-year': year,
                        text: annual.persentase == null ? '-' : `${Number(annual.persentase).toFixed(2)}%`,
                    })).appendTo($row);
                });
                $('<td>').append($('<span>', {
                    class: 'save-status text-muted small',
                    text: item.status === 'draft' && Object.keys(item.realisasi_tahunan || {}).length ? 'Draft tersimpan' : 'Belum disimpan',
                })).appendTo($row);
                $body.append($row);
            });
        }

        function loadData(periodeId) {
            if (!periodeId) {
                $('#unitInfo').addClass('d-none');
                showMessage('Pilih periode untuk memuat realisasi.');
                return;
            }
            $('#tableMessage').removeClass('d-none').text('Memuat data...');
            $('#tableWrapper').addClass('d-none');
            fetch(`${DATA_URL}?periode_id=${encodeURIComponent(periodeId)}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        renderTable(response);
                        if (IS_ADMIN) $('#realisasiTable .realization-input, #realisasiTable .semester-note, #realisasiTable .document-input').prop('disabled', true);
                        if (IS_ADMIN) $('#realisasiTable .upload-documents, #realisasiTable .delete-document').remove();
                    } else showMessage(response.message || 'Gagal memuat data.');
                })
                .catch(() => showMessage('Gagal memuat realisasi kinerja layanan.'));
        }

        function saveDraft($row) {
            const targetId = $row.data('target-id');
            const annual = {};
            responseYears.forEach(year => {
                annual[year] = { semester_1: {}, semester_2: {} };
                $row.find(`.realization-input[data-year="${year}"]`).each(function () {
                    annual[year][`semester_${$(this).data('semester')}`].nilai = $(this).val();
                });
                $row.find(`.semester-note[data-year="${year}"]`).each(function () {
                    annual[year][`semester_${$(this).data('semester')}`].keterangan = $(this).val();
                });
            });
            const $status = $row.find('.save-status').text('Menyimpan...').removeClass('text-danger').addClass('text-muted');
            fetch(DRAFT_URL, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }, body: JSON.stringify({ target_id: targetId, realisasi_tahunan: annual }) })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        $status.text('Draft tersimpan');
                        Object.entries(response.realisasi_tahunan || {}).forEach(([year, value]) => $row.find(`[data-percentage-year="${year}"]`).text(value.persentase == null ? '-' : `${Number(value.persentase).toFixed(2)}%`));
                    } else $status.text(response.message || 'Gagal menyimpan').removeClass('text-muted').addClass('text-danger');
                })
                .catch(() => $status.text('Gagal menyimpan').removeClass('text-muted').addClass('text-danger'));
        }

        let responseYears = [];
        $('#periode_id').on('change', function () { loadData(this.value); });
        $('#realisasiTable').on('input', '.realization-input, .semester-note', function () {
            const $row = $(this).closest('tr');
            if ($(this).hasClass('realization-input')) calculatePercentage($row, $(this).data('year'));
            const id = $row.data('target-id');
            clearTimeout(saveTimers[id]);
            $row.find('.save-status').text('Menunggu...');
            saveTimers[id] = setTimeout(() => saveDraft($row), 700);
        });
        $('#realisasiTable').on('click', '.upload-documents', function () {
            const $row = $(this).closest('tr');
            const year = $(this).data('year');
            const semester = $(this).data('semester');
            const input = $row.find(`.document-input[data-year="${year}"][data-semester="${semester}"]`)[0];
            if (!input.files.length) return showAlert('warning', 'Pilih dokumen terlebih dahulu.');
            const formData = new FormData();
            formData.append('target_id', $row.data('target-id')); formData.append('tahun', year); formData.append('semester', semester);
            Array.from(input.files).forEach(file => formData.append('dokumen[]', file));
            fetch(DOCUMENT_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }, body: formData })
                .then(response => response.json()).then(response => response.success ? (showAlert('success', response.message), loadData($('#periode_id').val())) : showAlert('error', response.message))
                .catch(() => showAlert('error', 'Dokumen gagal diunggah.'));
        });
        $('#realisasiTable').on('click', '.delete-document', function () {
            fetch(`${DOCUMENT_DELETE_URL}/${$(this).data('id')}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' } })
                .then(response => response.json()).then(response => response.success ? (showAlert('success', response.message), loadData($('#periode_id').val())) : showAlert('error', response.message))
                .catch(() => showAlert('error', 'Dokumen gagal dihapus.'));
        });
        if (ACTIVE_PERIOD_ID) loadData(ACTIVE_PERIOD_ID);
    });
</script>
@endpush
