<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UnitKerjaController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = UnitKerja::with('operator')->orderBy('kode_unit')->get();

                return response()->json(['data' => $data]);
            }
            $operators = User::where('role', 'operator')->orderBy('name')->get();

            return view('master.unit-kerja.index', compact('operators'));
        } catch (Exception $e) {
            Log::error('UnitKerja index error: '.$e->getMessage());
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
                'kode_unit' => 'required|string|max:50|unique:unit_kerja,kode_unit',
                'nama_unit' => 'required|string|max:255',
                'operator_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'operator'))],
            ], [
                'kode_unit.required' => 'Kode unit wajib diisi.',
                'kode_unit.unique' => 'Kode unit sudah digunakan.',
                'nama_unit.required' => 'Nama unit wajib diisi.',
                'operator_id.exists' => 'Operator tidak valid.',
            ]);

            UnitKerja::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Unit kerja berhasil ditambahkan.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('UnitKerja store error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $item = UnitKerja::findOrFail($id);

            $data = $request->validate([
                'kode_unit' => 'required|string|max:50|unique:unit_kerja,kode_unit,'.$id,
                'nama_unit' => 'required|string|max:255',
                'operator_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'operator'))],
            ], [
                'kode_unit.required' => 'Kode unit wajib diisi.',
                'kode_unit.unique' => 'Kode unit sudah digunakan.',
                'nama_unit.required' => 'Nama unit wajib diisi.',
                'operator_id.exists' => 'Operator tidak valid.',
            ]);

            $item->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Unit kerja berhasil diperbarui.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('UnitKerja update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = UnitKerja::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unit kerja berhasil dihapus.',
            ]);
        } catch (Exception $e) {
            Log::error('UnitKerja destroy error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data. Data mungkin masih digunakan.',
            ], 500);
        }
    }
}
