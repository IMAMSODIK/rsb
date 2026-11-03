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
            <li class="breadcrumb-item text-secondary" aria-current="page">Kategori RBA</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Kategori RBA</h5>
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
                                    <th>Jenis RBA</th>
                                    <th>Kode Kategori</th>
                                    <th>Uraian</th>
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
                        <h5 class="modal-title" id="modalTitle">Tambah Kategori RBA</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="mb-3">
                            <label class="form-label">Jenis RBA <span class="text-danger">*</span></label>
                            <select name="jenis_id" id="jenis_id" class="form-select" required>
                                <option value="">-- Pilih Jenis RBA --</option>
                                @foreach ($jenis as $item)
                                    <option value="{{ $item->id }}" data-kode="{{ $item->kode_jenis }}" title="{{ $item->uraian }}">{{ $item->uraian }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="kode_kategori" id="kode_kategori" class="form-control" placeholder="Contoh: K1.1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Uraian <span class="text-danger">*</span></label>
                            <input type="text" name="uraian" id="uraian" class="form-control" placeholder="Contoh: Pendapatan jasa pelayanan pasien umum" required>
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
        const BASE = '{{ route("rba.master.kategori.index") }}';
        let table;

        table = $('#datatable').DataTable({
            ajax: { url: BASE, dataSrc: 'data' },
            order: [[2, 'asc']],
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'jenis.kode_jenis', defaultContent: '-' },
                { data: 'kode_kategori' },
                { data: 'uraian' },
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
                emptyTable: 'Belum ada data kategori RBA',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        const modal = new bootstrap.Modal($('#formModal')[0]);

        $('#formModal').on('hidden.bs.modal', function () {
            $('#formData')[0].reset();
            $('#recordId').val('');
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah Kategori RBA');
        });

        $('#btnAdd').on('click', () => modal.show());

        // Prefix kode otomatis dari Jenis RBA terpilih.
        // Hanya mengisi saat tambah baru dan field masih kosong/belum diawali prefix.
        $('#jenis_id').on('change', function () {
            if ($('#recordId').val()) return;
            const kode = $(this).find('option:selected').data('kode');
            if (!kode) return;
            const $input = $('#kode_kategori');
            const prefix = String(kode).trim() + '.';
            const current = $input.val() || '';
            if (current === '' || !current.startsWith(prefix)) {
                $input.val(prefix);
            }
        });

        $('#datatable').on('click', '.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            $('#modalTitle').text('Edit Kategori RBA');
            $('#recordId').val(row.id);
            $('#jenis_id').val(row.jenis_id);
            $('#kode_kategori').val(row.kode_kategori);
            $('#uraian').val(row.uraian);
            modal.show();
        });

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
