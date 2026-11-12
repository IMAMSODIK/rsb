@extends('layouts.template')

@section('content')
    <style>
        #analisisTable { min-width: 1700px; }
        #analisisTable th { text-align: center; vertical-align: middle; white-space: nowrap; }
        #analisisTable td.numeric-cell, .analisis-group-table td.numeric-cell { text-align: right; white-space: nowrap; min-width: 170px; }
        #analisisTable td.hasil-cell, .analisis-group-table td.hasil-cell { background-color: #f8f9fa; font-weight: 600; }
        #inputTable { min-width: 1500px; }
        #inputTable th { text-align: center; vertical-align: middle; white-space: nowrap; }
        #inputTable td.numeric-cell { text-align: right; min-width: 200px; }
        #inputTable .form-control { text-align: right; }
        #inputTable td.var-cell, #inputTable th.var-cell { min-width: 220px; white-space: normal; }
        #analisisTable .target-input { min-width: 170px; }
        .fmt-wrap .input-group-text { font-size: .75rem; padding: .25rem .4rem; }
        .fmt-wrap .form-control { min-width: 0; text-align: right; }
        .save-status { font-size: .8rem; }
        #analisisTable thead th.sticky-col,
        #analisisTable tbody td.sticky-col,
        .analisis-group-table thead th.sticky-col,
        .analisis-group-table tbody td.sticky-col {
            position: sticky;
            z-index: 2;
            background-color: #fff;
        }
        #analisisTable thead th.sticky-col, .analisis-group-table thead th.sticky-col { z-index: 3; }
        #analisisTable tbody td.sticky-col.hasil-kode, .analisis-group-table tbody td.sticky-col.hasil-kode { background-color: #f8f9fa; }
        #analisisTable th.sticky-nomor, #analisisTable td.sticky-nomor,
        .analisis-group-table th.sticky-nomor, .analisis-group-table td.sticky-nomor { left: 0; min-width: 60px; }
        #analisisTable th.sticky-kode, #analisisTable td.sticky-kode,
        .analisis-group-table th.sticky-kode, .analisis-group-table td.sticky-kode { left: 60px; min-width: 140px; }
        .analisis-group-table th { text-align: center; vertical-align: middle; white-space: nowrap; }
        .analisis-group-table { min-width: 1400px; }
        #inputTable thead th.sticky-col,
        #inputTable tbody td.sticky-col {
            position: sticky;
            z-index: 2;
            background-color: #fff;
        }
        #inputTable thead th.sticky-col { z-index: 3; }
        #inputTable th.sticky-nomor, #inputTable td.sticky-nomor { left: 0; min-width: 60px; }
        #inputTable th.sticky-var, #inputTable td.sticky-var { left: 60px; }
        .rba-tabs .nav-link { font-weight: 600; }
        .rba-kpi { border-left: 4px solid #0d6efd; }
        .rba-kpi .kpi-label { font-size: .78rem; color: #6c757d; }
        .rba-kpi .kpi-value { font-size: 1.05rem; font-weight: 700; }
    </style>
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">RBA</li>
            <li class="breadcrumb-item text-secondary">Analisis</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Analisis RBA</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-block">
                    <div class="row">
                        <div class="col-12">
                            <h5 class="card-title mb-2">Analisis RBA (Target, Realisasi &amp; Prognosa)</h5>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="tahun_anggaran_id" class="form-label mb-1">Tahun</label>
                            <select id="tahun_anggaran_id" class="form-select form-select-sm" title="Pilih tahun anggaran">
                                <option value="">-- Pilih Tahun --</option>
                                @foreach ($tahun as $item)
                                    <option value="{{ $item->id }}" @selected($current?->id === $item->id)>
                                        {{ $item->tahun }} ({{ $item->status }}){{ $item->is_current ? ' - Berjalan' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="jenis" class="form-label mb-1">Jenis</label>
                            <select id="jenis" class="form-select form-select-sm" title="Filter jenis analisis">
                                <option value="">Semua Jenis</option>
                                @foreach ($jenisList as $jenis)
                                    <option value="{{ $jenis }}">{{ ucfirst($jenis) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <span id="saveStatus" class="save-status text-secondary"></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="yearInfo" class="alert alert-info d-none"></div>
                    <div id="overrideBox" class="alert alert-warning d-none">
                        <label class="form-label mb-1">Tahun berstatus <span id="overrideStatus"></span>. Alasan override (wajib, khusus admin):</label>
                        <input type="text" id="alasan" class="form-control" placeholder="Contoh: revisi data SDM disetujui direktur">
                    </div>
                    <div id="tableMessage" class="text-secondary">Pilih tahun untuk memuat analisis.</div>
                    <div id="tableWrapper" class="d-none">
                        <div id="kpiRow" class="row g-2 mb-3 d-none">
                            <div class="col-md-3 col-sm-6">
                                <div class="card rba-kpi h-100"><div class="card-body py-2">
                                    <div class="kpi-label">Jumlah Analisis</div>
                                    <div class="kpi-value" id="kpiJumlah">-</div>
                                </div></div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card rba-kpi h-100" style="border-color:#198754"><div class="card-body py-2">
                                    <div class="kpi-label">Rata-rata Capaian</div>
                                    <div class="kpi-value" id="kpiRata">-</div>
                                </div></div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card rba-kpi h-100" style="border-color:#ffc107"><div class="card-body py-2">
                                    <div class="kpi-label">Prognosa Terisi</div>
                                    <div class="kpi-value" id="kpiPrognosa">-</div>
                                </div></div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="card rba-kpi h-100" style="border-color:#6c757d"><div class="card-body py-2">
                                    <div class="kpi-label">Variabel Terisi (S1/S2)</div>
                                    <div class="kpi-value" id="kpiVariabel">-</div>
                                </div></div>
                            </div>
                        </div>
                        <ul class="nav nav-tabs rba-tabs mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-hasil" data-bs-toggle="tab" data-bs-target="#pane-hasil" type="button" role="tab">
                                    <i class="bi bi-clipboard-data"></i> Hasil Analisis
                                    <span class="badge bg-primary ms-1" id="badgeHasil">0</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-data" data-bs-toggle="tab" data-bs-target="#pane-data" type="button" role="tab">
                                    <i class="bi bi-pencil-square"></i> Data Mentah
                                    <span class="badge bg-secondary ms-1" id="badgeVariabel">0</span>
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="pane-hasil" role="tabpanel">
                                <p class="text-secondary small mb-2">Isi kolom Target langsung — tersimpan otomatis. Dikelompokkan per Jenis Analisis dan Kelompok Rasio.</p>
                                <div id="hasilGroups"></div>
                            </div>
                            <div class="tab-pane fade" id="pane-data" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <p class="text-secondary small mb-0">Target diinput per variabel; Realisasi &amp; Prognosa dihitung otomatis.</p>
                                    <div class="input-group input-group-sm" style="max-width: 280px">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="cariVariabel" class="form-control" placeholder="Cari variabel...">
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                                    <table class="table table-bordered table-hover align-middle" id="inputTable">
                                        <thead class="table-light" style="position: sticky; top: 0; z-index: 2;">
                                            <tr>
                                                <th width="50" class="sticky-col sticky-nomor">Nomor</th>
                                                <th class="sticky-col sticky-var var-cell">Variabel</th>
                                                <th>Target (tahunan)</th>
                                                <th>Realisasi (tahunan)</th>
                                                <th>Prognosa</th>
                                                <th>Capaian (Persen)</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        const DATA_URL = '{{ route("rba.analisis.data") }}';
        const SAVE_URL = '{{ route("rba.analisis.save") }}';
        const ACTIVE_YEAR_ID = @json($current?->id);
        const VARIABLES = @json($variables);
        const DEFINITIONS = @json($definitions);
        let saveTimer = null;
        let saving = false;
        let pendingSave = false;

        function showMessage(text) {
            $('#tableMessage').removeClass('d-none').text(text);
            $('#tableWrapper').addClass('d-none');
            $('#yearInfo').addClass('d-none');
            $('#overrideBox').addClass('d-none');
            $('#kpiRow').addClass('d-none');
        }

        function setStatus(text, cls) {
            const $el = $('#saveStatus');
            $el.removeClass('text-secondary text-success text-danger text-primary');
            $el.addClass(cls || 'text-secondary').text(text || '');
        }

        function fmtAngka(v, desimal) {
            if (v == null || v === '') return '-';
            return Number(v).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: desimal ?? 4 });
        }

        function fmtUang(v, desimal) {
            if (v == null || v === '') return '-';
            return 'Rp ' + Number(v).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: desimal ?? 2 });
        }

        function fmtOrang(v) {
            if (v == null || v === '') return '-';
            return Number(v).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' orang';
        }

        function fmtHasil(v, tipe) {
            if (v == null || v === '') return '-';
            if (tipe === 'currency') return fmtUang(v);
            if (tipe === 'orang' || tipe === 'cacah') return Number(v).toLocaleString('id-ID', { maximumFractionDigits: 2 });
            return fmtAngka(v, 4);
        }

        function fmtTampil(v, tipe) {
            if (v == null || v === '' || isNaN(Number(String(v).replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(',', '.')))) {
                if (v == null || v === '') return '';
            }
            const num = Number(v);
            if (isNaN(num)) return '';
            if (tipe === 'currency') return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            if (tipe === 'persen') return num.toLocaleString('id-ID', { maximumFractionDigits: 2 });
            // Kolom Target selalu tampil desimal (koma) bila ada;
            // pembulatan hanya untuk tipe *_bulat.
            return num.toLocaleString('id-ID', { maximumFractionDigits: 4 });
        }

        function parseTampil(str) {
            if (str == null) return null;
            const s = String(str).trim();
            if (s === '' || s === '-') return null;
            const bersih = s.replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(',', '.');
            if (bersih === '' || bersih === '-' || bersih === '.') return null;
            const num = Number(bersih);
            return isNaN(num) ? null : num;
        }

        function fmtLive(str, tipe) {
            if (str == null) return '';
            let s = String(str);
            const negatif = s.trim().startsWith('-');
            s = s.replace(/[^0-9,]/g, '');
            if (s === '') return '';
            const parts = s.split(',');
            let bulat = (parts[0] || '').replace(/^0+(?=\d)/, '');
            // Gabungkan semua digit desimal (abaikan koma ganda dari tempel),
            // lalu batasi 4 digit. Koma SELALU dipertahankan — kolom Target
            // dan rasio butuh desimal; pembulatan bulat hanya untuk tipe *_bulat.
            let des = parts.slice(1).join('').replace(/[^0-9]/g, '');
            const bulatOnly = tipe === 'orang_bulat' || tipe === 'cacah_bulat' || tipe === 'bulat';
            if (bulatOnly) des = '';
            else des = des.substring(0, 4);
            if (bulat === '') bulat = '0';
            bulat = bulat.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            // PENTING: jika user baru mengetik koma (parts.length > 1) tapi digit
            // desimal belum ada, koma WAJIB dipertahankan (mis. "40," tetap "40,")
            // agar bisa lanjut mengetik desimalnya. Tanpa ini koma terasa "tidak bisa diketik".
            const komaDiketik = !bulatOnly && parts.length > 1;
            let hasil = (des !== '' || komaDiketik) ? bulat + ',' + des : bulat;
            return negatif ? '-' + hasil : hasil;
        }

        function inputCell(tipe) {
            const prefix = tipe === 'currency' ? 'Rp' : null;
            const $wrap = $('<div>', { class: 'input-group input-group-sm fmt-wrap' });
            if (prefix) $('<span>', { class: 'input-group-text', text: prefix }).appendTo($wrap);
            $('<input>', { type: 'text', inputmode: 'decimal', class: 'form-control form-control-sm fmt-input', 'data-tipe': tipe || 'angka', autocomplete: 'off', placeholder: '0' }).appendTo($wrap);
            return $('<td>', { class: 'numeric-cell' }).append($wrap);
        }

        function setInputValue($cell, raw, tipe) {
            const $input = $cell.find('.fmt-input');
            $input.val(raw == null || raw === '' ? '' : fmtTampil(raw, tipe));
            $input.data('raw', raw == null || raw === '' ? null : Number(raw));
        }

        function renderTable(response) {
            $('#yearInfo').removeClass('d-none').text('Tahun: ' + response.tahun.tahun + ' | Status: ' + response.tahun.status + ' | Realisasi & prognosa dihitung otomatis.');
            if (response.tahun.locked) {
                $('#overrideBox').removeClass('d-none');
                $('#overrideStatus').text(response.tahun.status);
            } else {
                $('#overrideBox').addClass('d-none');
            }

            const $inBody = $('#inputTable tbody').empty();
            (response.variable_rows || []).forEach((vr, i) => {
                const $tr = $('<tr>').attr('data-var', vr.kode);
                $('<td>', { text: i + 1, class: 'text-center sticky-col sticky-nomor' }).appendTo($tr);
                $('<td>', { text: vr.label, class: 'sticky-col sticky-var var-cell' }).appendTo($tr);
                const $tc = inputCell(vr.tipe); setInputValue($tc, vr.target ?? null, vr.tipe); $tc.find('.fmt-input').addClass('variable-target-input'); $tr.append($tc);
                const $rc = inputCell(vr.tipe); setInputValue($rc, vr.realisasi ?? null, vr.tipe); $rc.find('.fmt-input').addClass('variable-realisasi-input').attr('data-semester', 0); $tr.append($rc);
                $('<td>', { text: fmtHasil(vr.prognosa, vr.tipe), class: 'numeric-cell hasil-cell' }).appendTo($tr);
                $('<td>', { text: (vr.capaian == null ? '-' : fmtAngka(vr.capaian, 2) + ' %'), class: 'numeric-cell hasil-cell' }).appendTo($tr);
                $inBody.append($tr);
            });

            const byKode = {};
            (response.rows || []).forEach(r => { byKode[r.kode] = r; });
            const jenisFilter = $('#jenis').val();
            let defs = DEFINITIONS;
            if (jenisFilter) defs = defs.filter(d => d.jenis === jenisFilter);
            const $groups = $('#hasilGroups').empty();
            const orderJenis = [];
            const groups = {};
            defs.forEach((d) => {
                const key = d.jenis + '||' + (d.kelompok || d.nama);
                if (!groups[key]) {
                    groups[key] = { jenis: d.jenis, kelompok: d.kelompok || d.nama, items: [] };
                    orderJenis.push(key);
                }
                groups[key].items.push(d);
            });
            let nomor = 0;
            orderJenis.forEach((key) => {
                const g = groups[key];
                const $card = $('<div>', { class: 'card mb-3' });
                const $head = $('<div>', { class: 'card-header d-flex justify-content-between align-items-center flex-wrap gap-2' });
                $('<div>').html('<span class="badge bg-info me-2">' + g.jenis.charAt(0).toUpperCase() + g.jenis.slice(1) + '</span><strong>' + g.kelompok + '</strong>').appendTo($head);
                $('<span>', { class: 'badge bg-secondary', text: g.items.length + ' analisis' }).appendTo($head);
                $card.append($head);
                const $wrap = $('<div>', { class: 'table-responsive' });
                const $table = $('<table>', { class: 'table table-bordered table-hover align-middle mb-0 analisis-group-table' });
                const $thead = $('<thead>').append($('<tr>')
                    .append($('<th>', { width: 50, text: 'Nomor', class: 'sticky-col sticky-nomor' }))
                    .append($('<th>', { text: 'Kode', class: 'sticky-col sticky-kode' }))
                    .append($('<th>', { text: 'Nama Analisis' }))
                    .append($('<th>', { text: 'Target (tahunan)' }))
                    .append($('<th>', { text: 'Realisasi (tahunan)' }))
                    .append($('<th>', { text: 'Prognosa' }))
                    .append($('<th>', { text: 'Capaian (Persen)' })));
                $table.append($thead);
                const $body = $('<tbody>');
                g.items.forEach((d) => {
                    nomor++;
                    const r = byKode[d.kode] || {};
                    const $tr = $('<tr>').attr('data-kode', d.kode);
                    $('<td>', { text: nomor, class: 'text-center sticky-col sticky-nomor' }).appendTo($tr);
                    $('<td>', { text: d.kode, class: 'sticky-col sticky-kode hasil-kode' }).appendTo($tr);
                    $('<td>', { text: d.nama }).appendTo($tr);
                    const $tc = inputCell('desimal'); setInputValue($tc, r.target ?? null, 'desimal'); $tc.find('.fmt-input').addClass('target-input'); $tr.append($tc);
                    $('<td>', { text: fmtHasil(r.realisasi ?? r.semester_1, d.tipe), class: 'numeric-cell hasil-cell' }).appendTo($tr);
                    $('<td>', { text: fmtHasil(r.prognosa, d.tipe), class: 'numeric-cell hasil-cell' }).appendTo($tr);
                    const cap = fmtAngka(r.capaian, 2);
                    $('<td>', { text: (r.capaian == null ? '-' : cap + ' %'), class: 'numeric-cell hasil-cell' }).appendTo($tr);
                    $body.append($tr);
                });
                $table.append($body);
                $wrap.append($table);
                $card.append($wrap);
                $groups.append($card);
            });

            $('#tableMessage').addClass('d-none');
            $('#tableWrapper').removeClass('d-none');
            $('#kpiRow').removeClass('d-none');
            renderKpi(response);
            setStatus('Siap. Perubahan tersimpan otomatis.', 'text-secondary');
        }

        function renderKpi(response) {
            const rows = response.rows || [];
            const vrows = response.variable_rows || [];
            const capaian = rows.map(r => r.capaian).filter(v => v != null);
            const rata = capaian.length ? capaian.reduce((a, b) => a + Number(b), 0) / capaian.length : null;
            const prognosaCount = rows.filter(r => r.prognosa != null).length;
            const targetCount = vrows.filter(v => v.target != null).length;
            const realisasiCount = vrows.filter(v => v.realisasi != null).length;
            $('#kpiJumlah').text(rows.length + ' analisis');
            $('#kpiRata').text(rata == null ? '-' : rata.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' %');
            $('#kpiPrognosa').text(prognosaCount + ' / ' + rows.length);
            $('#kpiVariabel').text(targetCount + ' target / ' + realisasiCount + ' realisasi');
            $('#badgeHasil').text(rows.length);
            $('#badgeVariabel').text(vrows.length || VARIABLES.length);
        }

        function loadData(yearId) {
            if (!yearId) { showMessage('Pilih tahun untuk memuat analisis.'); return; }
            $('#tableMessage').removeClass('d-none').text('Memuat data...');
            $('#tableWrapper').addClass('d-none');
            const jenis = $('#jenis').val();
            let url = DATA_URL + '?tahun_anggaran_id=' + yearId;
            if (jenis) url += '&jenis=' + encodeURIComponent(jenis);
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(r => { if (r.success) renderTable(r); else showMessage(r.message || 'Gagal memuat data.'); })
                .catch(() => showMessage('Gagal memuat analisis RBA.'));
        }

        function collectPayload() {
            const yearId = $('#tahun_anggaran_id').val();
            const inputs = [];
            const variableTargets = [];
            $('#inputTable tbody tr').each(function () {
                const kode = $(this).data('var');
                const $target = $(this).find('.variable-target-input');
                const parsedTarget = parseTampil($target.val());
                $target.data('raw', parsedTarget);
                variableTargets.push({ variabel_kode: kode, target: parsedTarget });
                // Realisasi tahunan variabel disimpan di semester 0.
                $(this).find('.variable-realisasi-input').each(function () {
                    const parsed = parseTampil($(this).val());
                    $(this).data('raw', parsed);
                    inputs.push({ variabel_kode: kode, semester: 0, nilai: parsed });
                });
            });
            const targets = [];
            $('#hasilGroups tr[data-kode] .target-input').each(function () {
                const $input = $(this);
                const parsed = parseTampil($input.val());
                $input.data('raw', parsed);
                targets.push({ analisis_kode: $input.closest('tr').data('kode'), target: parsed });
            });
            return { tahun_anggaran_id: yearId, alasan: $('#alasan').val() || null, inputs: inputs, variable_targets: variableTargets, targets: targets };
        }

        function refreshHasilSilently(rows, variableRows) {
            const vByKode = {};
            (variableRows || []).forEach(r => { vByKode[r.kode] = r; });
            $('#inputTable tbody tr').each(function () {
                const r = vByKode[$(this).data('var')];
                if (!r) return;
                const $cells = $(this).find('td.hasil-cell');
                // Kolom Realisasi kini input (semester 0) — jangan timpa nilai
                // yang sedang diketik; hanya refresh Prognosa & Capaian.
                $($cells[0]).text(fmtHasil(r.prognosa, r.tipe));
                $($cells[1]).text(r.capaian == null ? '-' : fmtAngka(r.capaian, 2) + ' %');
            });
            const byKode = {};
            (rows || []).forEach(r => { byKode[r.kode] = r; });
            $('#hasilGroups tr[data-kode]').each(function () {
                const kode = $(this).data('kode');
                const r = byKode[kode];
                if (!r) return;
                const def = DEFINITIONS.find(d => d.kode === kode) || {};
                const tipe = def.tipe || r.tipe;
                const $cells = $(this).find('td.hasil-cell');
                $($cells[0]).text(fmtHasil(r.realisasi ?? r.semester_1, tipe));
                $($cells[1]).text(fmtHasil(r.prognosa, tipe));
                $($cells[2]).text(r.capaian == null ? '-' : fmtAngka(r.capaian, 2) + ' %');
            });
        }

        function saveNow() {
            const yearId = $('#tahun_anggaran_id').val();
            if (!yearId) return;
            if (saving) { pendingSave = true; return; }
            saving = true;
            pendingSave = false;
            setStatus('Menyimpan...', 'text-primary');
            fetch(SAVE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(collectPayload()),
            })
                .then(r => r.json().then(j => ({ ok: r.ok, body: j })).catch(() => ({ ok: false, body: { message: 'Respon tidak valid.' } })))
                .then(res => {
                    saving = false;
                    if (res.ok && res.body.success) {
                        setStatus('Tersimpan otomatis ' + new Date().toLocaleTimeString('id-ID') + '.', 'text-success');
                        if (res.body.rows) refreshHasilSilently(res.body.rows, res.body.variable_rows);
                        else loadDataSilently(yearId);
                    } else {
                        setStatus(res.body.message || 'Gagal menyimpan otomatis.', 'text-danger');
                    }
                    if (pendingSave) scheduleSave();
                })
                .catch(() => { saving = false; setStatus('Gagal menyimpan otomatis.', 'text-danger'); if (pendingSave) scheduleSave(); });
        }

        function loadDataSilently(yearId) {
            const jenis = $('#jenis').val();
            let url = DATA_URL + '?tahun_anggaran_id=' + yearId;
            if (jenis) url += '&jenis=' + encodeURIComponent(jenis);
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(r => { if (r.success) refreshHasilSilently(r.rows); })
                .catch(() => {});
        }

        function scheduleSave() {
            clearTimeout(saveTimer);
            setStatus('Mengetik... akan tersimpan otomatis.', 'text-secondary');
            saveTimer = setTimeout(saveNow, 1200);
        }

        $('#tahun_anggaran_id, #jenis').on('change', function () { loadData($('#tahun_anggaran_id').val()); });

        $(document).on('input', '#inputTable .fmt-input, #hasilGroups .fmt-input', function () {
            // Baca atribut langsung (bukan .data()) agar tidak kena cache jQuery
            // saat baris di-render ulang — koma harus selalu lolos di kolom Target.
            const tipe = $(this).attr('data-tipe') || $(this).data('tipe');
            const pos = this.selectionStart;
            const before = this.value.length;
            this.value = fmtLive(this.value, tipe);
            try { this.setSelectionRange(pos + (this.value.length - before), pos + (this.value.length - before)); } catch (e) {}
            scheduleSave();
        });

        $(document).on('change', '#alasan', function () { scheduleSave(); });

        $(document).on('input', '#cariVariabel', function () {
            const q = $(this).val().toLowerCase();
            $('#inputTable tbody tr').each(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
            });
        });
        if (ACTIVE_YEAR_ID) loadData(ACTIVE_YEAR_ID);
    });
</script>
@endpush
