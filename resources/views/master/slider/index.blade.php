@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">Master</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Slider Login</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Slider Halaman Login</h5>
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
                                    <th width="120">Gambar</th>
                                    <th>Judul</th>
                                    <th width="80">Urutan</th>
                                    <th width="100">Status</th>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formData" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Slider</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" name="judul" id="judul" class="form-control" placeholder="Masukkan judul slide (opsional)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar <span class="text-danger" id="gambarRequired">*</span></label>
                            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Format jpg, jpeg, png, atau webp. Maksimal 2 MB.</small>
                            <div class="mt-2" id="gambarPreviewWrap" style="display:none;">
                                <img id="gambarPreview" src="#" alt="Preview" class="img-thumbnail" style="max-height:200px;">
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="urutan" id="urutan" class="form-control" placeholder="0" min="0">
                                <small class="text-muted">Angka lebih kecil tampil lebih dulu.</small>
                            </div>
                            <div class="mb-3 col-md-6 d-flex align-items-center pt-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                                    <label for="is_active" class="form-check-label">Tampilkan slide</label>
                                </div>
                            </div>
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
        const BASE = '{{ route("master.slider.index") }}';
        let table;

        table = $('#datatable').DataTable({
            ajax: { url: BASE, dataSrc: 'data' },
            order: [[3, 'asc']],
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                {
                    data: 'gambar',
                    className: 'text-center',
                    render: (val, type, row) => `<img src="${row.gambar_url}" alt="${row.judul || ''}" class="rounded" style="width:80px;height:50px;object-fit:cover;">`,
                    orderable: false
                },
                { data: 'judul', defaultContent: '<span class="text-muted">-</span>' },
                { data: 'urutan', className: 'text-center' },
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
                emptyTable: 'Belum ada data slider',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        const modal = new bootstrap.Modal($('#formModal')[0]);

        // Reset form saat modal ditutup
        $('#formModal').on('hidden.bs.modal', function () {
            $('#formData')[0].reset();
            $('#recordId').val('');
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah Slider');
            $('#gambar').attr('required', true);
            $('#gambarRequired').show();
            $('#gambarPreviewWrap').hide();
            $('#gambarPreview').attr('src', '#');
            $('#is_active').prop('checked', true);
        });

        // Preview gambar saat dipilih
        $('#gambar').on('change', function () {
            const file = this.files[0];
            if (file) {
                $('#gambarPreview').attr('src', URL.createObjectURL(file));
                $('#gambarPreviewWrap').show();
            } else {
                $('#gambarPreviewWrap').hide();
            }
        });

        // Tombol Tambah
        $('#btnAdd').on('click', () => modal.show());

        // Tombol Edit
        $('#datatable').on('click', '.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            $('#modalTitle').text('Edit Slider');
            $('#recordId').val(row.id);
            $('#judul').val(row.judul || '');
            $('#urutan').val(row.urutan ?? 0);
            $('#is_active').prop('checked', row.is_active == true || row.is_active == 1);
            // Saat edit, gambar opsional
            $('#gambar').attr('required', false);
            $('#gambarRequired').hide();
            // Tampilkan gambar saat ini sebagai preview
            if (row.gambar_url) {
                $('#gambarPreview').attr('src', row.gambar_url);
                $('#gambarPreviewWrap').show();
            } else {
                $('#gambarPreviewWrap').hide();
            }
            $('#formMethod').val('PUT');
            modal.show();
        });

        // Submit form
        $('#formData').on('submit', function (e) {
            e.preventDefault();
            const id = $('#recordId').val();
            const url = id ? `${BASE}/${id}` : BASE;
            const method = id ? 'PUT' : 'POST';

            // Checkbox is_active tidak ter-submit saat unchecked; pastikan eksplisit
            if (!$('#is_active').is(':checked')) {
                $('<input>').attr({ type: 'hidden', name: 'is_active', value: '0' }).appendTo(this);
            }

            submitForm(this, url, method, res => {
                modal.hide();
                showAlert('success', res.message);
                table.ajax.reload(null, false);
            });
        });

        // Tombol Hapus
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