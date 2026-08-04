<?php

namespace App\Http\Controllers;

use App\Models\SasaranProgram;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SasaranProgramController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = SasaranProgram::orderBy('kode_sasaran_program')->get();
                return response()->json(['data' => $data]);
            }
            return view('master.sasaran-program.index');
        } catch (Exception $e) {
            Log::error('SasaranProgram index error: ' . $e->getMessage());
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
                'kode_sasaran_program' => 'required|string|max:50|unique:sasaran_program,kode_sasaran_program',
                'uraian'               => 'required|string',
            ], [
                'kode_sasaran_program.required' => 'Kode sasaran program wajib diisi.',
                'kode_sasaran_program.unique'   => 'Kode sasaran program sudah digunakan.',
                'uraian.required'               => 'Uraian wajib diisi.',
            ]);

            SasaranProgram::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Sasaran program berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SasaranProgram store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = SasaranProgram::findOrFail($id);

            $data = $request->validate([
                'kode_sasaran_program' => 'required|string|max:50|unique:sasaran_program,kode_sasaran_program,' . $id,
                'uraian'               => 'required|string',
            ], [
                'kode_sasaran_program.required' => 'Kode sasaran program wajib diisi.',
                'kode_sasaran_program.unique'   => 'Kode sasaran program sudah digunakan.',
                'uraian.required'               => 'Uraian wajib diisi.',
            ]);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Sasaran program berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('SasaranProgram update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = SasaranProgram::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sasaran program berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('SasaranProgram destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
