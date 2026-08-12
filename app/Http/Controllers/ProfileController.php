<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                'name.required' => 'Nama wajib diisi.',
                'avatar.image' => 'File avatar harus berupa gambar.',
                'avatar.mimes' => 'Avatar harus berformat JPG, JPEG, PNG, atau WEBP.',
                'avatar.max' => 'Ukuran avatar maksimal 2 MB.',
            ]);

            $user = auth()->user();
            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
            } else {
                unset($data['avatar']);
            }

            $user->update($data);

            return back()->with('success', 'Profil berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }
}
