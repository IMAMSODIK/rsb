@extends('layouts.template')

@section('content')
    <div class="app-hero-header d-flex align-items-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <i class="bi bi-house lh-1 pe-3 me-3 border-end border-dark"></i>
                <a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item text-secondary">Master</li>
            <li class="breadcrumb-item text-secondary" aria-current="page">IKU Kinerja Layanan</li>
        </ol>
    </div>

    <div class="app-body">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Data IKU Kinerja Layanan</h5>
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
                                    <th>Kode IKU</th>
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

    {{-- Modal Tambah/Edit --}}
    <div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formData">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah IKU Kinerja Layanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="recordId">
                        <div class="mb-3">
                            <label class="form-label">Sasaran Strategis <span class="text-danger">*</span></label>
                            <select name="sasaran_strategis_id" id="sasaran_strategis_id" class="form-select" required>
                                <option value="">-- Pilih Sasaran Strategis --</option>
                                @foreach ($sasaranStrategis as $item)
                                    <option value="{{ $item->id }}" data-kode="{{ $item->kode_sasaran_strategis }}">{{ $item->kode_sasaran_strategis }} - {{ $item->uraian }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode IKU <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="kodePrefix">-</span>
                                <input type="text" name="kode_iku" id="kode_iku" class="form-control" placeholder="Kode tambahan" required>
                            </div>
                            <small class="text-muted">Prefix mengikuti kode Sasaran Strategis yang dipilih.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Uraian IKU <span class="text-danger">*</span></label>
                            <textarea name="uraian" id="uraian" class="form-control" rows="3" required></textarea>
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
        const BASE = '{{ route("master.iku-layanan.index") }}';
        let table;

        table = $('#datatable').DataTable({
            ajax: { url: BASE, dataSrc: 'data' },
            order: [[1, 'asc']],
            columns: [
                { data: null, render: (d, t, r, m) => m.row + 1 },
                { data: 'kode_iku' },
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
                emptyTable: 'Belum ada data IKU kinerja layanan',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        const modal = new bootstrap.Modal($('#formModal')[0]);

        function updateKodePrefix() {
            const kodeStrategis = $('#sasaran_strategis_id option:selected').data('kode');
            $('#kodePrefix').text(kodeStrategis ? `${kodeStrategis}.` : '-');
        }

        function setKodeSuffix(kodeIku, kodeStrategis) {
            const prefix = kodeStrategis ? `${kodeStrategis}.` : '';
            $('#kode_iku').val(
                prefix && kodeIku.startsWith(prefix) ? kodeIku.slice(prefix.length) : kodeIku
            );
        }

        $('#sasaran_strategis_id').on('change', updateKodePrefix);

        $('#formModal').on('hidden.bs.modal', function () {
            $('#formData')[0].reset();
            $('#recordId').val('');
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah IKU Kinerja Layanan');
            updateKodePrefix();
        });

        $('#btnAdd').on('click', () => modal.show());

        $('#datatable').on('click', '.btn-edit', function () {
            const row = table.row($(this).closest('tr')).data();
            $('#modalTitle').text('Edit IKU Kinerja Layanan');
            $('#recordId').val(row.id);
            $('#sasaran_strategis_id').val(row.sasaran_strategis_id);
            updateKodePrefix();
            setKodeSuffix(row.kode_iku, row.sasaran_strategis?.kode_sasaran_strategis);
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
