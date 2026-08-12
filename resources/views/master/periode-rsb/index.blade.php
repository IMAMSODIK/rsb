@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">Master</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Periode RSB</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Periode RSB</h5>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAdd">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Tahun Awal</th>
                                    <th>Tahun Akhir</th>
                                    <th>Status</th>
                                    <th width="120" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formData">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Periode RSB</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Tahun Awal <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_awal" id="tahun_awal" class="form-control"
                                    min="2000" max="2100" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Tahun Akhir <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_akhir" id="tahun_akhir" class="form-control"
                                    min="2000" max="2100" required>
                            </div>
                        </div>
                        <small class="text-muted">Periode mengikuti blok 5 tahunan, contoh: 2025 - 2029, 2020 - 2024.</small>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1">
                            <label for="is_active" class="form-check-label">Set sebagai periode aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        const BASE = '{{ route("master.periode-rsb.index") }}';
        let table;

        table = $('#datatable').DataTable({
            ajax: { url: BASE, dataSrc: 'data' },
            order: [[1, 'desc']],
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'tahun_awal' },
                { data: 'tahun_akhir' },
                {
                    data: 'is_active', className: 'text-center',
                    render: val => val
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Nonaktif</span>'
                },
                {
                    data: 'id', orderable: false, className: 'text-end',
                    render: id => `
                        <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${id}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${id}"><i class="bi bi-trash"></i></button>`
                }
            ],
            language: {
                search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data periode RSB',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        const modal = new bootstrap.Modal($('#formModal')[0]);

        function validatePeriodRange() {
            const tahunAwal = parseInt($('#tahun_awal').val(), 10);
            const tahunAkhir = parseInt($('#tahun_akhir').val(), 10);
            const valid = Number.isNaN(tahunAwal) || Number.isNaN(tahunAkhir)
                || (tahunAkhir - tahunAwal === 4 && tahunAwal % 5 === 0);
            $('#tahun_akhir')[0].setCustomValidity(valid ? '' : 'Periode harus mengikuti blok 5 tahunan, contoh 2025 - 2029.');
        }

        $('#tahun_awal, #tahun_akhir').on('input', validatePeriodRange);

        $('#formModal').on('hidden.bs.modal', function () {
            $('#formData')[0].reset();
            $('#recordId').val('');
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah Periode RSB');
        });

        $('#btnAdd').on('click', () => modal.show());

        $('#datatable').on('click', '.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            $('#modalTitle').text('Edit Periode RSB');
            $('#recordId').val(row.id);
            $('#tahun_awal').val(row.tahun_awal);
            $('#tahun_akhir').val(row.tahun_akhir);
            $('#is_active').prop('checked', row.is_active == true || row.is_active == 1);
            modal.show();
        });

        $('#formData').on('submit', function (e) {
            e.preventDefault();
            const id = $('#recordId').val();
            const url = id ? `${BASE}/${id}` : BASE;
            const method = id ? 'PUT' : 'POST';

            // Checkbox tidak ter-submit saat unchecked; pastikan eksplisit
            if (!$('#is_active').is(':checked')) {
                $('<input>').attr({ type: 'hidden', name: 'is_active', value: '0' }).appendTo(this);
            }

            submitForm(this, url, method, res => {
                modal.hide();
                showAlert('success', res.message);
                table.ajax.reload(null, false);
            });
        });

        $('#datatable').on('click', '.btn-delete', function () {
            const id = $(this).data('id');
            confirmDelete(() => deleteRecord(`${BASE}/${id}`, res => {
                showAlert('success', res.message);
                table.ajax.reload(null, false);
            }));
        });
    });
</script>
@endpush
