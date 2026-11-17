<?php

namespace App\Http\Controllers;

use App\Models\LogAudit;
use App\Models\PermintaanResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dasbor');
        }

        if (!$request->session()->has('human_answer')) {
            $this->generateHumanVerification($request);
        }

        return view('auth.login');
    }

    public function refreshCaptcha(Request $request)
    {
        $question = $this->generateHumanVerification($request);

        return response()->json([
            'success' => true,
            'question' => $question,
        ]);
    }

    private function generateHumanVerification(Request $request): string
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $answer = $num1 + $num2;
        $question = "$num1 + $num2 = ?";

        $request->session()->put('human_answer', $answer);
        $request->session()->put('human_question', $question);

        return $question;
    }

    public function login(Request $request)
    {
        $throttleKey = Str::transliterate(Str::lower((string) $request->input('username')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->generateHumanVerification($request);
            return back()->withErrors([
                'username' => "Terlalu banyak percobaan masuk yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('username');
        }

        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'human_verification' => ['required', 'integer'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'human_verification.required' => 'Verifikasi keamanan wajib diisi.',
            'human_verification.integer' => 'Jawaban verifikasi harus berupa angka.',
        ]);

        $expectedAnswer = (int) $request->session()->get('human_answer');
        $givenAnswer = (int) $request->input('human_verification');

        // Refresh soal tiap kali verifikasi dikirimkan untuk mencegah replay attack
        $this->generateHumanVerification($request);

        if ($givenAnswer !== $expectedAnswer) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors([
                'human_verification' => 'Jawaban verifikasi keamanan salah. Silakan hitung kembali soal yang baru.',
            ])->onlyInput('username');
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'aktif' => true], $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->forget(['human_answer', 'human_question']);
            $request->session()->regenerate();
            return redirect()->intended(route('dasbor'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'username' => 'Username atau password salah, atau akun Anda nonaktif.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('sukses', 'Anda telah berhasil keluar.');
    }

    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return redirect()->route('dasbor');
        }

        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'identitas' => ['required', 'string', 'max:150'],
        ], [
            'identitas.required' => 'Masukkan username atau alamat email Anda.',
        ]);

        $identitas = strtolower(trim($request->identitas));
        $user = User::where('aktif', true)
            ->where(function ($q) use ($identitas) {
                $q->whereRaw('LOWER(username) = ?', [$identitas])
                  ->orWhereRaw('LOWER(email) = ?', [$identitas]);
            })
            ->first();

        if (!$user) {
            return back()
                ->withInput(['identitas' => $request->identitas])
                ->withErrors(['identitas' => 'Akun dengan username/email tersebut tidak ditemukan atau dalam status nonaktif.']);
        }

        if (empty($user->email)) {
            return back()
                ->withInput(['identitas' => $request->identitas])
                ->withErrors(['identitas' => 'Akun ini belum memiliki alamat email terdaftar. Gunakan tab "Ajukan ke Super Admin".']);
        }

        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            LogAudit::create([
                'user_id' => $user->id,
                'aksi' => 'Kirim Tautan Reset Password',
                'entitas' => 'User',
                'entitas_id' => $user->id,
                'detail' => ['email' => $user->email, 'mailer' => config('mail.default')],
            ]);

            $infoLokal = config('mail.default') === 'log'
                ? ' Mode lokal Laragon (MAIL_MAILER=log) aktif: tautan reset dicatat pada storage/logs/laravel.log.'
                : '';

            return back()->with('sukses', "Tautan reset password telah dikirim ke email terdaftar ({$user->email}).{$infoLokal}");
        }

        return back()
            ->withInput(['identitas' => $request->identitas])
            ->withErrors(['identitas' => 'Tautan reset belum dapat dikirim saat ini (tunggu sebentar atau gunakan tab Ajukan ke Super Admin).']);
    }

    public function submitAdminResetRequest(Request $request)
    {
        $validated = $request->validate([
            'identitas_admin' => ['required', 'string', 'max:100'],
            'catatan_pemohon' => ['nullable', 'string', 'max:255'],
        ], [
            'identitas_admin.required' => 'Username atau NIP wajib diisi.',
        ]);

        $key = trim($validated['identitas_admin']);
        $user = User::where('aktif', true)
            ->where(function ($q) use ($key) {
                $q->whereRaw('LOWER(username) = ?', [strtolower($key)])
                  ->orWhere('nip', $key);
            })
            ->first();

        if (!$user) {
            return back()
                ->withInput($request->only('identitas_admin', 'catatan_pemohon'))
                ->with('active_tab', 'admin')
                ->withErrors(['identitas_admin' => 'Username atau NIP tidak ditemukan, atau akun Anda berstatus nonaktif.']);
        }

        $permintaan = PermintaanResetPassword::updateOrCreate(
            ['user_id' => $user->id, 'status' => 'Menunggu'],
            [
                'catatan_pemohon' => $validated['catatan_pemohon'] ?: 'Lupa password saat login.',
                'ip_address' => $request->ip(),
            ]
        );

        LogAudit::create([
            'user_id' => $user->id,
            'aksi' => 'Ajukan Reset Password ke Super Admin',
            'entitas' => 'PermintaanResetPassword',
            'entitas_id' => $permintaan->id,
            'detail' => ['username' => $user->username, 'ip' => $request->ip()],
        ]);

        return back()
            ->with('active_tab', 'admin')
            ->with('sukses', "Permohonan reset password untuk {$user->name} ({$user->username}) berhasil dicatat. Silakan hubungi Super Admin untuk menerima password baru.");
    }

    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                PermintaanResetPassword::where('user_id', $user->id)
                    ->where('status', 'Menunggu')
                    ->update([
                        'status' => 'Selesai',
                        'diproses_pada' => now(),
                    ]);

                LogAudit::create([
                    'user_id' => $user->id,
                    'aksi' => 'Reset Password via Token Email',
                    'entitas' => 'User',
                    'entitas_id' => $user->id,
                    'detail' => ['email' => $user->email],
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('sukses', 'Password Anda berhasil diperbarui. Silakan masuk dengan password baru.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Token reset password tidak valid atau sudah kedaluwarsa.']);
    }
}
