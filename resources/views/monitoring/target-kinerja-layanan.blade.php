@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">Monitoring</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Target Kinerja Layanan</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Target Kinerja Layanan</h5>
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
                                <option value="{{ $item->id }}" @selected($periodeAktif?->id === $item->id)>
                                    {{ $item->tahun_awal }} - {{ $item->tahun_akhir }}{{ $item->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div id="unitInfo" class="alert alert-info d-none"></div>
                    <div id="tableMessage" class="text-secondary">Pilih periode untuk memuat kategori kinerja layanan.</div>
                    <div id="targetTableWrapper" class="table-responsive d-none">
                        <table class="table table-hover align-middle" id="targetTable">
                            <thead>
                                <tr id="targetHeaders">
                                    <th width="60">No</th>
                                    <th>Unit Kerja</th>
                                    <th>Jenis Kinerja Layanan</th>
                                    <th>Kode Kategori</th>
                                    <th>Uraian</th>
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
        const DATA_URL = '{{ route("monitoring.target-kinerja-layanan.data") }}';
        const DRAFT_URL = '{{ route("monitoring.target-kinerja-layanan.draft") }}';
        const ACTIVE_PERIOD_ID = '{{ $periodeAktif?->id ?? '' }}';
        let years = [];
        let saveTimers = {};

        function showMessage(message) {
            $('#tableMessage').removeClass('d-none').text(message);
            $('#targetTableWrapper').addClass('d-none');
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
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data target kinerja layanan',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
            });
        }

        function renderTable(response) {
            destroyDataTable();
            years = response.years;
            $('#unitInfo').removeClass('d-none').text(
                `Unit kerja: ${response.unit_kerja.kode_unit} - ${response.unit_kerja.nama_unit} | Status penyimpanan: Draft otomatis`
            );
            $('#tableMessage').addClass('d-none');
            $('#targetTableWrapper').removeClass('d-none');

             const $headers = $('#targetHeaders').empty();
             $('<th>', { width: 60, text: 'No' }).appendTo($headers);
             $('<th>', { text: 'Unit Kerja' }).appendTo($headers);
             $('<th>', { text: 'Jenis Kinerja Layanan' }).appendTo($headers);
             $('<th>', { text: 'Kode Kategori' }).appendTo($headers);
            $('<th>', { text: 'Uraian' }).appendTo($headers);
            years.forEach(year => $('<th>', { text: `Target ${year}` }).appendTo($headers));
            $('<th>', { width: 120, text: 'Status' }).appendTo($headers);

            const $body = $('#targetTable tbody').empty();
            if (!response.data.length) {
                showMessage('Belum ada kategori kinerja layanan untuk unit kerja ini.');
                return;
            }

            response.data.forEach((item, index) => {
                 const $row = $('<tr>').attr({ 'data-category-id': item.id });
                 $('<td>', { text: index + 1 }).appendTo($row);
                 $('<td>', { text: item.unit_kerja || '-' }).appendTo($row);
                 $('<td>', { text: item.jenis_kinerja_layanan || '-' }).appendTo($row);
                 $('<td>', { text: item.kode_kategori }).appendTo($row);
                $('<td>', { text: item.uraian }).appendTo($row);

                years.forEach(year => {
                    const value = item.target_tahunan?.[year] ?? '';
                      $('<td>').append($('<input>', {
                           type: 'text',
                          class: 'form-control form-control-sm target-input',
                          'data-year': year,
                           value: value,
                      })).appendTo($row);
                });

                $('<td>').append($('<span>', {
                    class: 'save-status text-muted small',
                    text: item.status === 'draft' && Object.keys(item.target_tahunan || {}).length ? 'Draft tersimpan' : 'Belum disimpan',
                })).appendTo($row);
                 $body.append($row);
             });
             initDataTable();
         }

        function loadData(periodeId) {
            if (!periodeId) {
                $('#unitInfo').addClass('d-none');
                showMessage('Pilih periode untuk memuat kategori kinerja layanan.');
                return;
            }

            $('#tableMessage').removeClass('d-none').text('Memuat data...');
            $('#targetTableWrapper').addClass('d-none');
            const params = new URLSearchParams({ periode_id: periodeId });
            const unitKerjaId = $('#unit_kerja_id').val();
            if (unitKerjaId) params.append('unit_kerja_id', unitKerjaId);
            fetch(`${DATA_URL}?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        renderTable(response);
                    }
                    else showMessage(response.message || 'Gagal memuat data.');
                })
                .catch(() => showMessage('Gagal memuat data target kinerja layanan.'));
        }

        function saveDraft($row) {
            const periodeId = $('#periode_id').val();
            const categoryId = $row.data('category-id');
            const targetTahunan = {};
            $row.find('.target-input').each(function () {
                targetTahunan[$(this).data('year')] = $(this).val();
            });

            const $status = $row.find('.save-status').text('Menyimpan...').removeClass('text-danger').addClass('text-muted');
            fetch(DRAFT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    periode_id: periodeId,
                    kategori_id: categoryId,
                    target_tahunan: targetTahunan,
                }),
            })
                .then(response => response.json())
                .then(response => {
                    if (response.success) $status.text('Draft tersimpan');
                    else $status.text(response.message || 'Gagal menyimpan').removeClass('text-muted').addClass('text-danger');
                })
                .catch(() => $status.text('Gagal menyimpan').removeClass('text-muted').addClass('text-danger'));
        }

        $('#periode_id').on('change', function () {
            loadData(this.value);
        });
        $('#unit_kerja_id').on('change', function () {
            loadData($('#periode_id').val());
        });

        $('#targetTable').on('input', '.target-input', function () {
            const $row = $(this).closest('tr');
            const rowId = $row.data('category-id');
            clearTimeout(saveTimers[rowId]);
            $row.find('.save-status').text('Menunggu...');
            saveTimers[rowId] = setTimeout(() => saveDraft($row), 700);
        });

        if (ACTIVE_PERIOD_ID) {
            loadData(ACTIVE_PERIOD_ID);
        }
    });
</script>
@endpush
