<?php

namespace App\Http\Controllers;

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
                $data = TujuanRsb::orderBy('kode_tujuan')->get();
                return response()->json(['data' => $data]);
            }
            return view('master.tujuan-rsb.index');
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
            ], [
                'kode_tujuan.required' => 'Kode tujuan wajib diisi.',
                'kode_tujuan.unique'   => 'Kode tujuan sudah digunakan.',
                'uraian.required'      => 'Uraian wajib diisi.',
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
            ], [
                'kode_tujuan.required' => 'Kode tujuan wajib diisi.',
                'kode_tujuan.unique'   => 'Kode tujuan sudah digunakan.',
                'uraian.required'      => 'Uraian wajib diisi.',
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
