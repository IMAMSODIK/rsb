@extends('layouts.template')

@section('content')
    <style>
        #realisasiTable { min-width: 2200px; }
        #realisasiTable th { text-align: center; vertical-align: middle; white-space: nowrap; }
        #realisasiTable td.numeric-cell { text-align: right; white-space: nowrap; min-width: 168px; }
        #realisasiTable .rupiah-wrap .input-group-text { font-size: .75rem; padding: .25rem .4rem; }
        #realisasiTable .rupiah-wrap .form-control { min-width: 0; text-align: right; }
        #realisasiTable td.uraian-cell { min-width: 260px; }
    </style>

    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">RBA</li>
            <li class="breadcrumb-item text-secondary">Transaksi</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Realisasi RBA</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Realisasi RBA (Rupiah)</h5>
                    <div class="d-flex align-items-center gap-2">
                        <label for="tahun_anggaran_id" class="mb-0 text-nowrap">Tahun</label>
                        <select id="tahun_anggaran_id" class="form-select form-select-sm" style="min-width: 180px" title="Pilih tahun anggaran">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach ($tahun as $item)
                                <option value="{{ $item->id }}" @selected($current?->id === $item->id)>
                                    {{ $item->tahun }} ({{ $item->status }}){{ $item->is_current ? ' - Berjalan' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary btn-sm" id="btnSave">
                            <i class="bi bi-save"></i> Simpan Realisasi
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="yearInfo" class="alert alert-info d-none"></div>
                    <div id="overrideBox" class="alert alert-warning d-none">
                        <label class="form-label mb-1">Tahun berstatus <span id="overrideStatus"></span>. Alasan override (wajib, khusus admin):</label>
                        <input type="text" id="alasan" class="form-control" placeholder="Contoh: koreksi realisasi semester disetujui direktur">
                    </div>
                    <div id="tableMessage" class="text-secondary">Pilih tahun untuk memuat realisasi.</div>
                    <div id="tableWrapper" class="table-responsive d-none">
                        <table class="table table-bordered table-hover align-middle" id="realisasiTable">
                            <thead>
                                <tr>
                                    <th rowspan="3" width="50">No</th>
                                    <th rowspan="3">Kode Detail</th>
                                    <th rowspan="3">Uraian</th>
                                    <th id="thYears" colspan="1" class="text-center">Realisasi per Tahun</th>
                                    <th rowspan="3">Keterangan (tahun acuan)</th>
                                </tr>
                                <tr id="subYearRow"></tr>
                                <tr id="subSemRow"></tr>
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
        const DATA_URL = '{{ route("rba.transaksi.realisasi.data") }}';
        const SAVE_URL = '{{ route("rba.transaksi.realisasi.save") }}';
        const ACTIVE_YEAR_ID = '{{ $current?->id ?? '' }}';

        // Format Rupiah: 5000000.5 -> "Rp 5.000.000,50" (desimal hanya jika ada).
        function formatRupiah(value) {
            if (value == null || value === '') return '-';
            const num = Number(String(value).replace(/[^0-9.,-]/g, '').replace(/\.(?=.*\.)/g, '').replace(',', '.'));
            if (isNaN(num)) return '-';
            return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        // Nilai awal untuk input: 5000000.5 -> "5.000.000,50".
        function formatRibuan(value) {
            if (value == null || value === '') return '';
            const parts = String(value).split('.');
            const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            if (parts.length > 1) {
                const dec = parts[1].slice(0, 2).replace(/0+$/, '');
                return dec ? intPart + ',' + dec : intPart;
            }
            return intPart;
        }

        // Ketikan user -> format ribuan otomatis: "1500000" -> "1.500.000".
        function formatLive(str) {
            const clean = String(str || '').replace(/[^0-9,]/g, '');
            if (!clean) return '';
            const parts = clean.split(',');
            const intPart = parts[0].replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            if (parts.length > 1) return intPart + ',' + parts.slice(1).join('').slice(0, 2);
            return intPart;
        }

        // "5.000.000,50" -> "5000000.50" (siap dikirim ke server).
        function parseRupiah(str) {
            if (str == null) return null;
            const s = String(str).trim();
            if (s === '') return null;
            const numeric = s.replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(',', '.');
            if (numeric === '' || numeric === '-' || isNaN(Number(numeric))) return null;
            return numeric;
        }

        function showMessage(message) {
            $('#tableMessage').removeClass('d-none').text(message);
            $('#tableWrapper').addClass('d-none');
        }

        function destroyDataTable() {
            if ($.fn.DataTable.isDataTable('#realisasiTable')) {
                $('#realisasiTable').DataTable().destroy();
            }
        }

        function initDataTable() {
            $('#realisasiTable').DataTable({
                order: [],
                scrollX: true,
                language: {
                    search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data realisasi RBA',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
            });
        }

        function percentText(value) {
            return value == null ? '-' : `${Number(value).toFixed(2)}%`;
        }

        function renderTable(response) {
            destroyDataTable();
            $('#yearInfo').removeClass('d-none').text(
                `Tahun acuan: ${response.tahun.tahun} | Status: ${response.tahun.status} | Persentase = (S1+S2)/Target x 100% | Kolom tahun acuan dapat diubah, tahun lain read-only`
            );
            if (response.tahun.locked) {
                $('#overrideStatus').text(response.tahun.status);
                $('#overrideBox').removeClass('d-none');
            } else {
                $('#overrideBox').addClass('d-none');
            }
            $('#tableMessage').addClass('d-none');
            $('#tableWrapper').removeClass('d-none');

            // Header: grup "Realisasi per Tahun" -> sub-kolom tahun -> sub-sub S1/S2/Total/Pencapaian.
            const years = response.years || [];
            const SUBS = [
                { key: 'target', label: 'Target (Rp)' },
                { key: 'semester_1', label: 'S1 (Rp)' },
                { key: 'semester_2', label: 'S2 (Rp)' },
                { key: 'total', label: 'Total (Rp)' },
                { key: 'persentase', label: '%' },
            ];
            $('#thYears').attr('colspan', Math.max(years.length * SUBS.length, 1));
            const $subYear = $('#subYearRow').empty();
            const $subSem = $('#subSemRow').empty();
            years.forEach(y => {
                const label = y.exists
                    ? `${y.tahun}${y.selected ? ' ★' : ''} (${y.status})`
                    : `${y.tahun} (belum ada)`;
                $('<th>', {
                    text: label, colspan: SUBS.length,
                    class: 'text-center' + (y.selected ? ' table-primary' : ''),
                }).appendTo($subYear);
                SUBS.forEach(s => $('<th>', {
                    text: s.label, class: 'text-center' + (y.selected ? ' table-primary' : ''),
                }).appendTo($subSem));
            });

            const $body = $('#realisasiTable tbody').empty();
            if (!response.data.length) {
                showMessage('Belum ada target RBA untuk tahun ini.');
                return;
            }

            response.data.forEach((item, index) => {
                const perTahun = {};
                Object.entries(item.per_tahun || {}).forEach(([k, v]) => { perTahun[String(k)] = v; });
                const selCell = perTahun[String(response.tahun.tahun)] || {};
                const targetId = selCell.target_id;
                const $row = $('<tr>').attr('data-target-id', targetId || '');
                $('<td>', { text: index + 1 }).appendTo($row);
                $('<td>', { text: item.kode_detail }).appendTo($row);
                $('<td>', { text: item.uraian, class: 'uraian-cell' }).appendTo($row);
                years.forEach(y => {
                    const cell = perTahun[String(y.tahun)] || {};
                    const editable = !!y.selected && y.exists;
                    $('<td>', { class: 'numeric-cell', text: formatRupiah(cell.target) }).appendTo($row);
                    [1, 2].forEach(sem => {
                        const key = sem === 1 ? 'semester_1' : 'semester_2';
                        const $td = $('<td>', { class: 'numeric-cell' });
                        const $wrap = $('<div>', { class: 'input-group input-group-sm rupiah-wrap' });
                        $('<span>', { class: 'input-group-text', text: 'Rp' }).appendTo($wrap);
                        $wrap.append($('<input>', {
                            type: 'text', inputmode: 'decimal',
                            class: 'form-control text-end semester-input rupiah-input' + (y.selected ? '' : ' bg-light'),
                            'data-semester': sem, 'data-tahun': y.tahun,
                            value: formatRibuan(cell[key]),
                            placeholder: y.exists ? '750.000.000' : 'Tahun belum ada',
                            disabled: !editable,
                            readonly: !editable,
                            title: y.exists ? (`S${sem} tahun ` + y.tahun + (cell[key] != null ? ': ' + formatRupiah(cell[key]) : '')) : ('Tahun ' + y.tahun + ' belum terdaftar'),
                        }));
                        $td.append($wrap).appendTo($row);
                    });
                    $('<td>', { class: 'numeric-cell total-cell', text: formatRupiah(cell.total) }).appendTo($row);
                    $('<td>', { class: 'numeric-cell percent-cell', text: percentText(cell.persentase ?? null) }).appendTo($row);
                });
                $('<td>').append($('<input>', {
                    type: 'text',
                    class: 'form-control form-control-sm note-input',
                    value: selCell.keterangan || '',
                    placeholder: 'Contoh: realisasi sesuai SP2D terbit',
                })).appendTo($row);
                $body.append($row);
            });
            initDataTable();
        }

        function loadData(yearId) {
            if (!yearId) {
                $('#yearInfo').addClass('d-none');
                $('#overrideBox').addClass('d-none');
                showMessage('Pilih tahun untuk memuat realisasi.');
                return;
            }
            $('#tableMessage').removeClass('d-none').text('Memuat data...');
            $('#tableWrapper').addClass('d-none');
            fetch(`${DATA_URL}?tahun_anggaran_id=${yearId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(response => response.json())
                .then(response => {
                    if (response.success) renderTable(response);
                    else showMessage(response.message || 'Gagal memuat data.');
                })
                .catch(() => showMessage('Gagal memuat realisasi RBA.'));
        }

        function saveAll() {
            const yearId = $('#tahun_anggaran_id').val();
            if (!yearId) return showAlert('warning', 'Pilih tahun terlebih dahulu.');
            const rows = [];
            $('#realisasiTable tbody tr').each(function () {
                const $row = $(this);
                const targetId = $row.data('target-id');
                if (!targetId) return; // baris tanpa target di tahun acuan: read-only
                const s1 = parseRupiah($row.find('.semester-input[data-semester="1"]').val());
                const s2 = parseRupiah($row.find('.semester-input[data-semester="2"]').val());
                rows.push({
                    target_id: $row.data('target-id'),
                    semester_1: s1,
                    semester_2: s2,
                    keterangan: $row.find('.note-input').val() || null,
                });
            });
            fetch(SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ tahun_anggaran_id: yearId, rows: rows, alasan: $('#alasan').val() || null }),
            })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadData(yearId);
                    } else showAlert('error', response.message || 'Gagal menyimpan.');
                })
                .catch(() => showAlert('error', 'Gagal menyimpan realisasi RBA.'));
        }

        $('#tahun_anggaran_id').on('change', function () { loadData(this.value); });
        $('#btnSave').on('click', saveAll);

        $('#realisasiTable').on('input', '.rupiah-input', function () {
            const pos = this.selectionStart;
            const before = this.value.length;
            this.value = formatLive(this.value);
            try { this.setSelectionRange(pos + (this.value.length - before), pos + (this.value.length - before)); } catch (e) {}
        });

        if (ACTIVE_YEAR_ID) loadData(ACTIVE_YEAR_ID);
    });
</script>
@endpush
