<?php

namespace App\Http\Controllers;

use App\Models\PeriodeRsb;
use App\Models\TujuanRsb;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TujuanRsbController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = TujuanRsb::with('periode')->orderBy('kode_tujuan')->get()
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'kode_tujuan' => $item->kode_tujuan,
                        'uraian' => $item->uraian,
                        'periode_rsb_id' => $item->periode_rsb_id,
                        'periode_label' => $item->periode
                            ? $item->periode->tahun_awal.' - '.$item->periode->tahun_akhir.($item->periode->is_active ? ' (Aktif)' : '')
                            : null,
                    ]);
                return response()->json(['data' => $data]);
            }
            $periode = PeriodeRsb::orderBy('tahun_awal')->get();
            return view('master.tujuan-rsb.index', compact('periode'));
        } catch (Exception $e) {
            Log::error('TujuanRsb index error: ' . $e->getMessage());
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
                'kode_tujuan' => 'required|string|max:50|unique:tujuan_rsb,kode_tujuan',
                'uraian'      => 'required|string',
                'periode_rsb_id' => 'nullable|exists:periode_rsb,id',
            ], [
                'kode_tujuan.required' => 'Kode tujuan wajib diisi.',
                'kode_tujuan.unique'   => 'Kode tujuan sudah digunakan.',
                'uraian.required'      => 'Uraian wajib diisi.',
                'periode_rsb_id.exists' => 'Periode tidak valid.',
            ]);

            TujuanRsb::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Tujuan RSB berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('TujuanRsb store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = TujuanRsb::findOrFail($id);

            $data = $request->validate([
                'kode_tujuan' => 'required|string|max:50|unique:tujuan_rsb,kode_tujuan,' . $id,
                'uraian'      => 'required|string',
                'periode_rsb_id' => 'nullable|exists:periode_rsb,id',
            ], [
                'kode_tujuan.required' => 'Kode tujuan wajib diisi.',
                'kode_tujuan.unique'   => 'Kode tujuan sudah digunakan.',
                'uraian.required'      => 'Uraian wajib diisi.',
                'periode_rsb_id.exists' => 'Periode tidak valid.',
            ]);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Tujuan RSB berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('TujuanRsb update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = TujuanRsb::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tujuan RSB berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('TujuanRsb destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
