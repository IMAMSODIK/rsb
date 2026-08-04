<?php

namespace App\Http\Controllers;

use App\Models\SasaranStrategis;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SasaranStrategisController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = SasaranStrategis::orderBy('kode_sasaran_strategis')->get();
                return response()->json(['data' => $data]);
            }
            return view('master.sasaran-strategis.index');
        } catch (Exception $e) {
            Log::error('SasaranStrategis index error: ' . $e->getMessage());
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
                'kode_sasaran_strategis' => 'required|string|max:50|unique:sasaran_strategis,kode_sasaran_strategis',
                'uraian'                 => 'required|string',
            ], [
                'kode_sasaran_strategis.required' => 'Kode sasaran strategis wajib diisi.',
                'kode_sasaran_strategis.unique'   => 'Kode sasaran strategis sudah digunakan.',
                'uraian.required'                 => 'Uraian wajib diisi.',
            ]);

            SasaranStrategis::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Sasaran strategis berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SasaranStrategis store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = SasaranStrategis::findOrFail($id);

            $data = $request->validate([
                'kode_sasaran_strategis' => 'required|string|max:50|unique:sasaran_strategis,kode_sasaran_strategis,' . $id,
                'uraian'                 => 'required|string',
            ], [
                'kode_sasaran_strategis.required' => 'Kode sasaran strategis wajib diisi.',
                'kode_sasaran_strategis.unique'   => 'Kode sasaran strategis sudah digunakan.',
                'uraian.required'                 => 'Uraian wajib diisi.',
            ]);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Sasaran strategis berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SasaranStrategis update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = SasaranStrategis::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sasaran strategis berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('SasaranStrategis destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
