<?php

namespace App\Http\Controllers;

use App\Models\JenisKinerjaLayanan;
use App\Models\KategoriKinerjaLayanan;
use App\Models\PeriodeRsb;
use App\Models\UnitKerja;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class KategoriLayananController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $periodeId = $request->query('periode_rsb_id');
                $data = KategoriKinerjaLayanan::with(['layanan', 'unitKerja'])
                    ->when($periodeId, fn ($query) => $query->whereHas('layanan', fn ($q) => $q->where(function ($sub) use ($periodeId) {
                        $sub->whereNull('periode_rsb_id')->orWhere('periode_rsb_id', $periodeId);
                    })))
                    ->orderBy('kode_kategori')->get();
                return response()->json(['data' => $data]);
            }
            $layanan = JenisKinerjaLayanan::orderBy('kode_layanan')->get();
            $unitKerja = UnitKerja::orderBy('kode_unit')->get();
            $periode = PeriodeRsb::orderBy('tahun_awal')->get();
            $periodeAktif = $periode->firstWhere('is_active', true);
            return view('master.kategori-layanan.index', compact('layanan', 'unitKerja', 'periode', 'periodeAktif'));
        } catch (Exception $e) {
            Log::error('KategoriLayanan index error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memuat data.'], 500);
            }
            return redirect()->back()->with('error', 'Gagal memuat halaman.');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'layanan_id'    => 'required|exists:jenis_kinerja_layanan,id',
                'kode_kategori' => 'required|string|max:50|unique:kategori_kinerja_layanan,kode_kategori',
                'uraian'        => 'required|string',
                'unit_kerja_id' => 'nullable|exists:unit_kerja,id',
            ], [
                'layanan_id.required'    => 'Jenis layanan wajib dipilih.',
                'layanan_id.exists'      => 'Jenis layanan tidak valid.',
                'kode_kategori.required' => 'Kode kategori wajib diisi.',
                'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
                'uraian.required'        => 'Uraian wajib diisi.',
                'unit_kerja_id.exists'   => 'Unit kerja tidak valid.',
            ]);

            KategoriKinerjaLayanan::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Kategori kinerja layanan berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('KategoriLayanan store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = KategoriKinerjaLayanan::findOrFail($id);

            $data = $request->validate([
                'layanan_id'    => 'required|exists:jenis_kinerja_layanan,id',
                'kode_kategori' => 'required|string|max:50|unique:kategori_kinerja_layanan,kode_kategori,' . $id,
                'uraian'        => 'required|string',
                'unit_kerja_id' => 'nullable|exists:unit_kerja,id',
            ], [
                'layanan_id.required'    => 'Jenis layanan wajib dipilih.',
                'layanan_id.exists'      => 'Jenis layanan tidak valid.',
                'kode_kategori.required' => 'Kode kategori wajib diisi.',
                'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
                'uraian.required'        => 'Uraian wajib diisi.',
                'unit_kerja_id.exists'   => 'Unit kerja tidak valid.',
            ]);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Kategori kinerja layanan berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('KategoriLayanan update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = KategoriKinerjaLayanan::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori kinerja layanan berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('KategoriLayanan destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
