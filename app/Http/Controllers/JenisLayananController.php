<?php

namespace App\Http\Controllers;

use App\Models\JenisKinerjaLayanan;
use App\Models\PeriodeRsb;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class JenisLayananController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = JenisKinerjaLayanan::with('periode')->orderBy('kode_layanan')->get()
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'kode_layanan' => $item->kode_layanan,
                        'uraian' => $item->uraian,
                        'periode_rsb_id' => $item->periode_rsb_id,
                        'periode_label' => $item->periode
                            ? $item->periode->tahun_awal.' - '.$item->periode->tahun_akhir.($item->periode->is_active ? ' (Aktif)' : '')
                            : null,
                    ]);
                return response()->json(['data' => $data]);
            }
            $periode = PeriodeRsb::orderBy('tahun_awal')->get();
            return view('master.jenis-layanan.index', compact('periode'));
        } catch (Exception $e) {
            Log::error('JenisLayanan index error: ' . $e->getMessage());
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
                'kode_layanan' => 'required|string|max:50|unique:jenis_kinerja_layanan,kode_layanan',
                'uraian'       => 'required|string',
                'periode_rsb_id' => 'nullable|exists:periode_rsb,id',
            ], [
                'kode_layanan.required' => 'Kode layanan wajib diisi.',
                'kode_layanan.unique'   => 'Kode layanan sudah digunakan.',
                'uraian.required'       => 'Uraian wajib diisi.',
                'periode_rsb_id.exists' => 'Periode tidak valid.',
            ]);

            JenisKinerjaLayanan::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Jenis kinerja layanan berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('JenisLayanan store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = JenisKinerjaLayanan::findOrFail($id);

            $data = $request->validate([
                'kode_layanan' => 'required|string|max:50|unique:jenis_kinerja_layanan,kode_layanan,' . $id,
                'uraian'       => 'required|string',
                'periode_rsb_id' => 'nullable|exists:periode_rsb,id',
            ], [
                'kode_layanan.required' => 'Kode layanan wajib diisi.',
                'kode_layanan.unique'   => 'Kode layanan sudah digunakan.',
                'uraian.required'       => 'Uraian wajib diisi.',
                'periode_rsb_id.exists' => 'Periode tidak valid.',
            ]);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Jenis kinerja layanan berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('JenisLayanan update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = JenisKinerjaLayanan::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jenis kinerja layanan berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('JenisLayanan destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
