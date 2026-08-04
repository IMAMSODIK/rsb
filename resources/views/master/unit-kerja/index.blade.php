@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary" aria-current="page">
                Master
            </li>
            <li class="breadcrumb-item text-secondary" aria-current="page">
                Unit Kerja
            </li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Unit Kerja</h5>
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
                                    <th>Kode Unit</th>
                                    <th>Nama Unit</th>
                                    <th>Operator</th>
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
                        <h5 class="modal-title" id="modalTitle">Tambah Unit Kerja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="mb-3">
                            <label class="form-label">Kode Unit <span class="text-danger">*</span></label>
                            <input type="text" name="kode_unit" id="kode_unit" class="form-control" placeholder="Masukkan kode unit" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                            <input type="text" name="nama_unit" id="nama_unit" class="form-control" placeholder="Masukkan nama unit" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Operator</label>
                            <select name="operator_id" id="operator_id" class="form-select">
                                <option value="">-- Pilih Operator (opsional) --</option>
                                @foreach ($operators as $op)
                                    <option value="{{ $op->id }}">{{ $op->name }}</option>
                                @endforeach
                            </select>
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
        const BASE = '{{ route("master.unit-kerja.index") }}';
        let table;

        // Inisialisasi DataTable
        table = $('#datatable').DataTable({
            ajax: {
                url: BASE,
                dataSrc: 'data',
                error: function () {
                    showAlert('error', 'Gagal memuat data unit kerja.');
                }
            },
            order: [[1, 'asc']],
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'kode_unit' },
                { data: 'nama_unit' },
                {
                    data: 'operator.name',
                    defaultContent: '<span class="text-muted">-</span>'
                },
                {
                    data: 'id',
                    orderable: false,
                    className: 'text-end',
                    render: function (id) {
                        return `
                            <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${id}">
                                <i class="bi bi-trash"></i>
                            </button>`;
                    }
                }
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data unit kerja',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        const modal = new bootstrap.Modal($('#formModal')[0]);

        // Reset form saat modal ditutup
        $('#formModal').on('hidden.bs.modal', function () {
            $('#formData')[0].reset();
            $('#recordId').val('');
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah Unit Kerja');
        });

        // Tombol Tambah
        $('#btnAdd').on('click', function () {
            modal.show();
        });

        // Tombol Edit
        $('#datatable').on('click', '.btn-edit', function () {
            const id = $(this).data('id');
            const row = table.row($(this).closest('tr')).data();
            $('#modalTitle').text('Edit Unit Kerja');
            $('#recordId').val(row.id);
            $('#kode_unit').val(row.kode_unit);
            $('#nama_unit').val(row.nama_unit);
            $('#operator_id').val(row.operator_id || '');
            $('#formMethod').val('PUT');
            modal.show();
        });

        // Submit form
        $('#formData').on('submit', function (e) {
            e.preventDefault();
            const id = $('#recordId').val();
            const url = id ? `${BASE}/${id}` : BASE;
            const method = id ? 'PUT' : 'POST';

            // Spoof method via _method field. HTTP method fetch HARUS 'POST'
            // karena PHP hanya mem-parsing body FormData untuk POST.
            const fd = new FormData(this);
            if (method !== 'POST') {
                fd.set('_method', method);
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    modal.hide();
                    showAlert('success', res.message);
                    table.ajax.reload(null, false);
                } else {
                    showAlert('error', res.message || 'Terjadi kesalahan.');
                }
            })
            .catch(() => showAlert('error', 'Gagal menyimpan data.'));
        });

        // Tombol Hapus
        $('#datatable').on('click', '.btn-delete', function () {
            const id = $(this).data('id');
            confirmDelete(() => {
                fetch(`${BASE}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showAlert('success', res.message);
                        table.ajax.reload(null, false);
                    } else {
                        showAlert('error', res.message || 'Gagal menghapus data.');
                    }
                })
                .catch(() => showAlert('error', 'Gagal menghapus data.'));
            });
        });
    });
</script>
@endpush
