@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">Master</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">Kategori Kinerja Layanan</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data Kategori Kinerja Layanan</h5>
                    <div class="d-flex align-items-center gap-2">
                        <label for="periode_filter" class="mb-0 text-nowrap">Periode</label>
                        <select id="periode_filter" class="form-select form-select-sm" style="min-width: 180px">
                            <option value="">Semua Periode</option>
                            @foreach ($periode as $item)
                                <option value="{{ $item->id }}" @selected($periodeAktif?->id === $item->id)>
                                    {{ $item->tahun_awal }} - {{ $item->tahun_akhir }}{{ $item->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary btn-sm" id="btnAdd">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Jenis Layanan</th>
                                    <th>Kode Kategori</th>
                                    <th>Uraian</th>
                                    <th>Unit Kerja (Operator)</th>
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
                <form id="formData">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Kategori Kinerja Layanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="mb-3">
                            <label class="form-label">Jenis Kinerja Layanan <span class="text-danger">*</span></label>
                            <select name="layanan_id" id="layanan_id" class="form-select" required>
                                <option value="">-- Pilih Jenis Layanan --</option>
                                @foreach ($layanan as $item)
                                    <option value="{{ $item->id }}">{{ $item->kode_layanan }} - {{ $item->uraian }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="kode_kategori" id="kode_kategori" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Uraian Kategori <span class="text-danger">*</span></label>
                            <textarea name="uraian" id="uraian" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit Kerja / Operator</label>
                            <select name="unit_kerja_id" id="unit_kerja_id" class="form-select">
                                <option value="">-- Pilih Unit Kerja (opsional) --</option>
                                @foreach ($unitKerja as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->kode_unit }} - {{ $unit->nama_unit }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih unit kerja yang bertugas mengisi realisasi.</small>
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
        const BASE = '{{ route("master.kategori-layanan.index") }}';
        let table;

        table = $('#datatable').DataTable({
            ajax: {
                url: BASE,
                data: d => { d.periode_rsb_id = $('#periode_filter').val(); },
                dataSrc: 'data',
            },
            order: [[2, 'asc']],
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'layanan.kode_layanan', defaultContent: '-' },
                { data: 'kode_kategori' },
                { data: 'uraian' },
                {
                    data: 'unit_kerja.nama_unit',
                    defaultContent: '<span class="text-muted">-</span>'
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
                emptyTable: 'Belum ada data kategori kinerja layanan',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        const modal = new bootstrap.Modal($('#formModal')[0]);

        $('#formModal').on('hidden.bs.modal', function () {
            $('#formData')[0].reset();
            $('#recordId').val('');
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah Kategori Kinerja Layanan');
        });

        $('#btnAdd').on('click', () => modal.show());

        $('#periode_filter').on('change', () => table.ajax.reload(null, false));

        $('#datatable').on('click', '.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            $('#modalTitle').text('Edit Kategori Kinerja Layanan');
            $('#recordId').val(row.id);
            $('#layanan_id').val(row.layanan_id);
            $('#kode_kategori').val(row.kode_kategori);
            $('#uraian').val(row.uraian);
            $('#unit_kerja_id').val(row.unit_kerja_id || '');
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
