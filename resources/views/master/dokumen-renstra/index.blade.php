@php
    $isAdmin = auth()->user()->role === 'admin';
@endphp
@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">Master</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Dokumen Renstra</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Dokumen Renstra</h5>
                    @if($isAdmin)
                    <button type="button" class="btn btn-primary btn-sm" id="btnAdd">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th width="100" class="text-center">Tahun</th>
                                    <th>Uraian</th>
                                    <th width="280">Dokumen</th>
                                    @if($isAdmin)
                                    <th width="120" class="text-end">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
    {{-- Modal Tambah/Edit --}}
    <div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formData" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Dokumen Renstra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="mb-3">
                            <label class="form-label">Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" id="tahun" class="form-control" placeholder="cth: 2026" min="2000" max="2100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Uraian <span class="text-danger">*</span></label>
                            <textarea name="uraian" id="uraian" class="form-control" rows="4" placeholder="Masukkan uraian dokumen renstra" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Dokumen <span class="text-danger" id="fileRequired">*</span></label>
                            <input type="file" name="file" id="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                            <small class="text-muted">Format pdf, doc, docx, xls, atau xlsx. Maksimal 10 MB.</small>
                            <div class="mt-2" id="currentFileWrap" style="display:none;">
                                <small class="text-muted d-block">File saat ini:</small>
                                <a href="#" id="currentFileLink" target="_blank" rel="noopener" class="small"></a>
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
    @endif
@endsection

@push('scripts')
<script>
    $(function () {
        const BASE = '{{ route("master.dokumen-renstra.index") }}';
        const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
        let table;

        const formatUkuran = bytes => {
            if (!bytes) return '';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0, v = bytes;
            while (v >= 1024 && i < units.length - 1) { v /= 1024; i++; }
            return (Math.round(v * 10) / 10) + ' ' + units[i];
        };

        const columns = [
            { data: null, render: (d, t, r, m) => m.row + 1, orderable: false, searchable: false },
            { data: 'tahun', className: 'text-center' },
            { data: 'uraian' },
            {
                data: null, orderable: false,
                render: (d, type, row) => `
                    <a href="${row.url}" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width:240px;" title="${row.nama_asli}">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i>${row.nama_asli}
                    </a>
                    <div class="small text-muted">${formatUkuran(row.ukuran)}</div>`
            }
        ];

        if (isAdmin) {
            columns.push({
                data: 'id', orderable: false, searchable: false, className: 'text-end',
                render: id => `
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${id}"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${id}"><i class="bi bi-trash"></i></button>`
            });
        }

        table = $('#datatable').DataTable({
            ajax: { url: BASE, dataSrc: 'data' },
            order: [[1, 'desc']],
            columns: columns,
            language: {
                search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data', zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada dokumen renstra',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        @if($isAdmin)
        const modal = new bootstrap.Modal($('#formModal')[0]);

        // Reset form saat modal ditutup
        $('#formModal').on('hidden.bs.modal', function () {
            $('#formData')[0].reset();
            $('#recordId').val('');
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah Dokumen Renstra');
            $('#file').attr('required', true);
            $('#fileRequired').show();
            $('#currentFileWrap').hide();
            $('#currentFileLink').attr('href', '#').text('');
        });

        // Tombol Tambah
        $('#btnAdd').on('click', () => modal.show());

        // Tombol Edit
        $('#datatable').on('click', '.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            $('#modalTitle').text('Edit Dokumen Renstra');
            $('#recordId').val(row.id);
            $('#tahun').val(row.tahun);
            $('#uraian').val(row.uraian);
            // Saat edit, file opsional
            $('#file').attr('required', false);
            $('#fileRequired').hide();
            // Tampilkan file saat ini
            if (row.url) {
                $('#currentFileLink').attr('href', row.url).text(row.nama_asli);
                $('#currentFileWrap').show();
            } else {
                $('#currentFileWrap').hide();
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
        @endif
    });
</script>
@endpush
