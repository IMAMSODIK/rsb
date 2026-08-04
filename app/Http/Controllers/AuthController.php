<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\SliderItem;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function login()
    {
        try {
            $sliderItems = SliderItem::where('is_active', true)
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();

            return view('auth.login', compact('sliderItems'));
        } catch (Exception $e) {
            Log::error('Gagal memuat halaman login: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat halaman. Silakan coba lagi.');
        }
    }

    /**
     * Proses autentikasi login (AJAX).
     */
    public function authenticate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Kata sandi wajib diisi.',
                'password.min' => 'Kata sandi minimal 6 karakter.',
            ]);

            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            if (! Auth::attempt($credentials, $remember)) {
                // Cek apakah email terdaftar
                $user = User::where('email', $request->email)->first();

                if (! $user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun dengan email tersebut tidak ditemukan.',
                    ], 404);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Email atau kata sandi salah.',
                ], 401);
            }

            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'redirect' => route('dashboard'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('Gagal proses login: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        try {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
        } catch (Exception $e) {
            Log::error('Gagal proses logout: '.$e->getMessage());

            return redirect()->route('login')->with('error', 'Gagal logout. Silakan coba lagi.');
        }
    }

    // ============================================================
    // Lupa Password - Kirim OTP
    // ============================================================

    /**
     * Menampilkan halaman lupa password.
     */
    public function showForgotPassword()
    {
        try {
            $sliderItems = SliderItem::where('is_active', true)
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();

            return view('auth.forgot-password', compact('sliderItems'));
        } catch (Exception $e) {
            Log::error('Gagal memuat halaman lupa password: '.$e->getMessage());

            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat memuat halaman. Silakan coba lagi.');
        }
    }

    /**
     * Mengirim OTP ke email user (AJAX).
     */
    public function sendOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
            ]);

            $email = $request->email;

            // Cek apakah email terdaftar
            $user = User::where('email', $email)->first();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak terdaftar dalam sistem.',
                ], 404);
            }

            // Rate limiting: 1 request per 60 detik per email
            $rateLimitKey = 'otp-send:'.$email;
            if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
                $seconds = RateLimiter::availableIn($rateLimitKey);

                return response()->json([
                    'success' => false,
                    'message' => "Terlalu banyak request. Silakan coba lagi dalam {$seconds} detik.",
                ], 429);
            }
            RateLimiter::hit($rateLimitKey, 60);

            // Generate OTP 6 digit
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(15);

            // Simpan OTP ke database (nonaktifkan OTP lama)
            OtpCode::where('email', $email)->whereNull('used_at')->update(['used_at' => now()]);

            OtpCode::create([
                'email' => $email,
                'code' => $code,
                'expires_at' => $expiresAt,
            ]);

            // Kirim email OTP
            Mail::to($email)->send(new OtpMail($code, $expiresAt->format('d M Y, H:i')));

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP berhasil dikirim ke email Anda.',
                'redirect' => route('verify-otp', ['email' => $email]),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('Gagal mengirim OTP: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    // ============================================================
    // Verifikasi OTP
    // ============================================================

    /**
     * Menampilkan halaman verifikasi OTP.
     */
    public function showVerifyOtp(Request $request)
    {
        try {
            $email = $request->query('email');

            if (! $email) {
                return redirect()->route('forgot-password')->with('error', 'Email tidak ditemukan. Silakan ulangi dari awal.');
            }

            // Validasi email terdaftar
            $user = User::where('email', $email)->first();
            if (! $user) {
                return redirect()->route('forgot-password')->with('error', 'Email tidak terdaftar dalam sistem.');
            }

            $sliderItems = SliderItem::where('is_active', true)
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();

            return view('auth.verify-otp', compact('email', 'sliderItems'));
        } catch (Exception $e) {
            Log::error('Gagal memuat halaman verifikasi OTP: '.$e->getMessage());

            return redirect()->route('forgot-password')->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Verifikasi kode OTP (AJAX).
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string|size:6',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'otp.required' => 'Kode OTP wajib diisi.',
                'otp.size' => 'Kode OTP harus terdiri dari 6 digit.',
            ]);

            $email = $request->email;
            $otp = $request->otp;

            // Cari OTP yang valid
            $otpRecord = OtpCode::where('email', $email)
                ->where('code', $otp)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->first();

            if (! $otpRecord) {
                // Cek apakah OTP ada tapi sudah expired
                $expiredRecord = OtpCode::where('email', $email)
                    ->where('code', $otp)
                    ->whereNull('used_at')
                    ->first();

                if ($expiredRecord && $expiredRecord->isExpired()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode OTP sudah kadaluarsa. Silakan minta kode baru.',
                    ], 410);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid.',
                ], 401);
            }

            // Tandai OTP sebagai digunakan
            $otpRecord->markAsUsed();

            // Buat token sementara untuk reset password
            $token = bin2hex(random_bytes(32));

            // Simpan token ke cache (berlaku 15 menit)
            Cache::put('reset-token:'.$email, $token, now()->addMinutes(15));

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi OTP berhasil.',
                'redirect' => route('reset-password', [
                    'email' => $email,
                    'token' => $token,
                ]),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('Gagal verifikasi OTP: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    // ============================================================
    // Reset Password
    // ============================================================

    /**
     * Menampilkan halaman reset password.
     */
    public function showResetPassword(Request $request)
    {
        try {
            $email = $request->query('email');
            $token = $request->query('token');

            if (! $email || ! $token) {
                return redirect()->route('forgot-password')->with('error', 'Akses tidak valid. Silakan ulangi dari awal.');
            }

            // Validasi token
            $cachedToken = Cache::get('reset-token:'.$email);
            if (! $cachedToken || $cachedToken !== $token) {
                return redirect()->route('forgot-password')->with('error', 'Token tidak valid atau sudah kadaluarsa. Silakan ulangi dari awal.');
            }

            // Validasi email terdaftar
            $user = User::where('email', $email)->first();
            if (! $user) {
                return redirect()->route('forgot-password')->with('error', 'Email tidak terdaftar dalam sistem.');
            }

            $sliderItems = SliderItem::where('is_active', true)
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();

            return view('auth.reset-password', compact('email', 'token', 'sliderItems'));
        } catch (Exception $e) {
            Log::error('Gagal memuat halaman reset password: '.$e->getMessage());

            return redirect()->route('forgot-password')->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Proses reset password (AJAX).
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required|string|min:6',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'token.required' => 'Token tidak valid.',
                'password.required' => 'Kata sandi baru wajib diisi.',
                'password.min' => 'Kata sandi minimal 6 karakter.',
                'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
                'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
                'password_confirmation.min' => 'Konfirmasi kata sandi minimal 6 karakter.',
            ]);

            $email = $request->email;
            $token = $request->token;

            // Validasi token
            $cachedToken = Cache::get('reset-token:'.$email);
            if (! $cachedToken || $cachedToken !== $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau sudah kadaluarsa. Silakan ulangi dari awal.',
                ], 401);
            }

            // Update password
            $user = User::where('email', $email)->first();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun tidak ditemukan.',
                ], 404);
            }

            $user->update([
                'password' => $request->password,
            ]);

            // Hapus token yang sudah digunakan
            Cache::forget('reset-token:'.$email);

            // Nonaktifkan semua OTP untuk email ini
            OtpCode::where('email', $email)->whereNull('used_at')->update(['used_at' => now()]);

            // Logout semua session user ini
            // (opsional, hanya jika ingin memaksa re-login)

            return response()->json([
                'success' => true,
                'message' => 'Kata sandi berhasil diubah. Silakan login dengan kata sandi baru.',
                'redirect' => route('login'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->first()[0],
            ], 422);
        } catch (Exception $e) {
            Log::error('Gagal reset password: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset kata sandi. Silakan coba lagi nanti.',
            ], 500);
        }
    }
}
