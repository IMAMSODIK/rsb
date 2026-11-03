@extends('layouts.template')

@section('content')
    <style>
        #targetTable { min-width: 1500px; }
        #targetTable th { text-align: center; vertical-align: middle; white-space: nowrap; }
        #targetTable td.rupiah-cell { min-width: 168px; }
        #targetTable .rupiah-wrap .input-group-text { font-size: .75rem; padding: .25rem .4rem; }
        #targetTable .rupiah-wrap .form-control { min-width: 0; text-align: right; }
    </style>
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">RBA</li>
            <li class="breadcrumb-item text-secondary">Transaksi</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Target RBA</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Target RBA (Rupiah)</h5>
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
                            <i class="bi bi-save"></i> Simpan Target
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="yearInfo" class="alert alert-info d-none"></div>
                    <div id="overrideBox" class="alert alert-warning d-none">
                        <label class="form-label mb-1">Tahun berstatus <span id="overrideStatus"></span>. Alasan override (wajib, khusus admin):</label>
                        <input type="text" id="alasan" class="form-control" placeholder="Contoh: revisi pagu disetujui direktur">
                    </div>
                    <div id="tableMessage" class="text-secondary">Pilih tahun untuk memuat detail RBA.</div>
                    <div id="tableWrapper" class="table-responsive d-none">
                        <table class="table table-hover table-bordered align-middle" id="targetTable">
                            <thead>
                                <tr>
                                    <th rowspan="2" width="50">No</th>
                                    <th rowspan="2">Kode Detail</th>
                                    <th rowspan="2">Uraian</th>
                                    <th rowspan="2">Indikator</th>
                                    <th rowspan="2">Kategori</th>
                                    <th rowspan="2">Jenis</th>
                                    <th id="thYears" colspan="1" class="text-center">Target per Tahun (Rp)</th>
                                </tr>
                                <tr id="subYearRow"></tr>
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
        const DATA_URL = '{{ route("rba.transaksi.target.data") }}';
        const SAVE_URL = '{{ route("rba.transaksi.target.save") }}';
        const ACTIVE_YEAR_ID = '{{ $current?->id ?? '' }}';
        let saveTimers = {};
        let locked = false;

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
            if ($.fn.DataTable.isDataTable('#targetTable')) {
                $('#targetTable').DataTable().destroy();
            }
        }

        function initDataTable() {
            $('#targetTable').DataTable({
                order: [],
                scrollX: true,
                language: {
                    search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data target RBA',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
            });
        }

        function renderTable(response) {
            destroyDataTable();
            locked = !!response.tahun.locked;
            $('#yearInfo').removeClass('d-none').text(
                `Tahun acuan: ${response.tahun.tahun} | Status: ${response.tahun.status} | Semua nilai dalam Rupiah | Kolom tahun acuan dan +1 dapat diubah, tahun lain read-only`
            );
            if (locked) {
                $('#overrideStatus').text(response.tahun.status);
                $('#overrideBox').removeClass('d-none');
            } else {
                $('#overrideBox').addClass('d-none');
            }
            $('#tableMessage').addClass('d-none');
            $('#tableWrapper').removeClass('d-none');

            // Header: grup "Target per Tahun" + sub-kolom tiap tahun window.
            const years = response.years || [];
            const selectedPlusOne = Number(response.tahun.tahun) + 1;
            window._nextYearId = null;
            years.forEach(y => { if (y.tahun === selectedPlusOne && y.exists) window._nextYearId = y.id; });
            $('#thYears').attr('colspan', Math.max(years.length, 1));
            const $sub = $('#subYearRow').empty();
            years.forEach(y => {
                const label = y.exists
                    ? `${y.tahun}${y.selected ? ' ★' : ''} (${y.status})`
                    : `${y.tahun} (belum ada)`;
                $('<th>', { text: label, class: 'text-center' + (y.selected ? ' table-primary' : '') }).appendTo($sub);
            });

            const $body = $('#targetTable tbody').empty();
            if (!response.data.length) {
                showMessage('Belum ada detail RBA. Tambahkan master detail terlebih dahulu.');
                return;
            }

            response.data.forEach((item, index) => {
                const $row = $('<tr>').attr('data-detail-id', item.id);
                $('<td>', { text: index + 1 }).appendTo($row);
                $('<td>', { text: item.kode_detail }).appendTo($row);
                $('<td>', { text: item.uraian }).appendTo($row);
                $('<td>', { text: (item.kode_indikator ? item.kode_indikator + ' - ' : '') + (item.indikator || '-') }).appendTo($row);
                $('<td>', { text: (item.kode_kategori ? item.kode_kategori + ' - ' : '') + (item.kategori || '-') }).appendTo($row);
                $('<td>', { text: (item.kode_jenis ? item.kode_jenis + ' - ' : '') + (item.jenis || '-') }).appendTo($row);
                const perTahun = item.per_tahun || {};
                years.forEach(y => {
                    const val = perTahun[y.tahun] ?? perTahun[String(y.tahun)] ?? null;
                    // Kolom acuan dan +1 bisa diinput; tahun lain read-only.
                    const editable = (y.selected || y.tahun === selectedPlusOne) && y.exists;
                    const $cell = $('<td>', { class: 'rupiah-cell' });
                    const $wrap = $('<div>', { class: 'input-group input-group-sm rupiah-wrap' });
                    $('<span>', { class: 'input-group-text', text: 'Rp' }).appendTo($wrap);
                    $wrap.append($('<input>', {
                        type: 'text', inputmode: 'decimal',
                        class: 'form-control text-end target-input rupiah-input' + (y.selected ? '' : ' bg-light'),
                        'data-tahun': y.tahun,
                        value: formatRibuan(val),
                        placeholder: y.exists ? '1.500.000.000' : 'Tahun belum ada',
                        disabled: !editable,
                        readonly: !editable,
                        title: (y.exists ? 'Target tahun ' + y.tahun + (val != null ? ': ' + formatRupiah(val) : '') : 'Tahun ' + y.tahun + ' belum terdaftar'),
                    }));
                    $cell.append($wrap).appendTo($row);
                });
                $body.append($row);
            });
            initDataTable();
        }

        function loadData(yearId) {
            if (!yearId) {
                $('#yearInfo').addClass('d-none');
                $('#overrideBox').addClass('d-none');
                showMessage('Pilih tahun untuk memuat detail RBA.');
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
                .catch(() => showMessage('Gagal memuat target RBA.'));
        }

        function collectPayload() {
            const targets = [];
            const nextTargets = [];
            $('#targetTable tbody tr').each(function () {
                const $inputs = $(this).find('.target-input:not([disabled])');
                const $acuan = $inputs.first();
                targets.push({
                    detail_id: $(this).data('detail-id'),
                    nilai: parseRupiah($acuan.val()),
                });
                if ($inputs.length > 1) {
                    nextTargets.push({
                        detail_id: $(this).data('detail-id'),
                        nilai: parseRupiah($($inputs[1]).val()),
                    });
                }
            });
            const payload = {
                tahun_anggaran_id: $('#tahun_anggaran_id').val(),
                targets: targets,
                alasan: $('#alasan').val() || null,
            };
            const nextId = window._nextYearId || null;
            if (nextId && nextTargets.length) {
                payload.next_tahun_anggaran_id = nextId;
                payload.next_targets = nextTargets;
                payload.next_alasan = $('#alasan').val() || null;
            }
            return payload;
        }

        function saveAll() {
            const yearId = $('#tahun_anggaran_id').val();
            if (!yearId) return showAlert('warning', 'Pilih tahun terlebih dahulu.');
            fetch(SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(collectPayload()),
            })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        showAlert('success', response.message);
                        loadData(yearId);
                    } else showAlert('error', response.message || 'Gagal menyimpan.');
                })
                .catch(() => showAlert('error', 'Gagal menyimpan target RBA.'));
        }

        $('#tahun_anggaran_id').on('change', function () { loadData(this.value); });
        $('#btnSave').on('click', saveAll);

        $('#targetTable').on('input', '.target-input', function () {
            const pos = this.selectionStart;
            const before = this.value.length;
            this.value = formatLive(this.value);
            try { this.setSelectionRange(pos + (this.value.length - before), pos + (this.value.length - before)); } catch (e) {}
            const key = $(this).closest('tr').data('detail-id');
            clearTimeout(saveTimers[key]);
            saveTimers[key] = setTimeout(saveAll, 1500);
        });

        if (ACTIVE_YEAR_ID) loadData(ACTIVE_YEAR_ID);
    });
</script>
@endpush
