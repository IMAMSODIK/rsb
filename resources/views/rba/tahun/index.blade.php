@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">RBA</li>
            <li class="breadcrumb-item text-secondary">Master</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Tahun Anggaran</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Tahun Anggaran</h5>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAdd">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Status: <strong>OPEN</strong> dapat diubah semua user, <strong>LOCKED/CLOSED</strong> hanya admin dengan alasan (tercatat sebagai override).
                    </div>
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Tahun</th>
                                    <th>Status</th>
                                    <th>Tahun Berjalan</th>
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

    <div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formData">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Tahun Anggaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="mb-3">
                            <label class="form-label">Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" id="tahun" class="form-control" min="2000" max="2100" placeholder="Contoh: 2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="OPEN">OPEN</option>
                                <option value="LOCKED">LOCKED</option>
                                <option value="CLOSED">CLOSED</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_current" id="is_current" class="form-check-input" value="1">
                            <label for="is_current" class="form-check-label">Set sebagai tahun berjalan</label>
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
        const BASE = '{{ route("rba.master.tahun.index") }}';
        let table;

        table = $('#datatable').DataTable({
            ajax: { url: BASE, dataSrc: 'data' },
            order: [[1, 'desc']],
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'tahun' },
                {
                    data: 'status',
                    render: val => val === 'OPEN'
                        ? '<span class="badge bg-success">OPEN</span>'
                        : (val === 'LOCKED' ? '<span class="badge bg-warning text-dark">LOCKED</span>' : '<span class="badge bg-secondary">CLOSED</span>')
                },
                {
                    data: 'is_current', className: 'text-center',
                    render: val => (val == true || val == 1)
                        ? '<span class="badge bg-primary">Berjalan</span>'
                        : '<span class="text-muted">-</span>'
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
                emptyTable: 'Belum ada data tahun anggaran',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        const modal = new bootstrap.Modal($('#formModal')[0]);

        $('#formModal').on('hidden.bs.modal', function () {
            $('#formData')[0].reset();
            $('#recordId').val('');
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah Tahun Anggaran');
        });

        $('#btnAdd').on('click', () => modal.show());

        $('#datatable').on('click', '.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            $('#modalTitle').text('Edit Tahun Anggaran');
            $('#recordId').val(row.id);
            $('#tahun').val(row.tahun);
            $('#status').val(row.status);
            $('#is_current').prop('checked', row.is_current == true || row.is_current == 1);
            modal.show();
        });

        $('#formData').on('submit', function (e) {
            e.preventDefault();
            const id = $('#recordId').val();
            const url = id ? `${BASE}/${id}` : BASE;
            const method = id ? 'PUT' : 'POST';

            if (!$('#is_current').is(':checked')) {
                $('<input>').attr({ type: 'hidden', name: 'is_current', value: '0' }).appendTo(this);
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
