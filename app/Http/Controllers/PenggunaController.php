<?php

namespace App\Http\Controllers;

use App\Models\LogAudit;
use App\Models\PermintaanResetPassword;
use App\Models\UnitPengolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    private function checkAccess(): void
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Akses terbatas untuk Super Admin.');
        }
    }

    private function validateOperatorUnit(array $validated): ?\Illuminate\Http\RedirectResponse
    {
        if ($validated['peran'] === 'Operator' && empty($validated['unit_pengolah_id'])) {
            return back()->withErrors([
                'unit_pengolah_id' => 'Operator wajib ditautkan ke salah satu Unit Pengolah/Bidang.',
            ])->withInput();
        }

        return null;
    }

    public function index(Request $request)
    {
        $this->checkAccess();

        $query = User::with('unitPengolah');

        if ($request->filled('peran')) {
            $query->where('peran', $request->peran);
        }

        if ($request->filled('unit_pengolah_id')) {
            $query->where('unit_pengolah_id', $request->unit_pengolah_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $like = $this->likeOperator();
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('name', $like, "%{$q}%")
                    ->orWhere('username', $like, "%{$q}%")
                    ->orWhere('nip', $like, "%{$q}%")
                    ->orWhere('email', $like, "%{$q}%");
            });
        }

        $users = $query->orderBy('id', 'asc')->paginate(15)->withQueryString();
        $units = UnitPengolah::where('aktif', true)->orderBy('nama')->get();
        $resetRequests = PermintaanResetPassword::with('user')
            ->where('status', 'Menunggu')
            ->latest()
            ->get();

        return view('pengguna.index', compact('users', 'units', 'resetRequests'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username',
            'nip' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'peran' => 'required|in:SuperAdmin,Operator',
            'unit_pengolah_id' => 'nullable|exists:unit_pengolah,id',
        ]);

        if ($redirect = $this->validateOperatorUnit($validated)) {
            return $redirect;
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'nip' => $validated['nip'] ?? null,
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'peran' => $validated['peran'],
            'unit_pengolah_id' => $validated['unit_pengolah_id'] ?? null,
            'aktif' => true,
        ]);

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Tambah Akun Petugas',
            'entitas' => 'User',
            'entitas_id' => $user->id,
            'detail' => ['username' => $user->username, 'peran' => $user->peran],
        ]);

        return redirect()->route('pengguna.index')->with('sukses', "Akun pengguna {$user->name} ({$user->username}) berhasil dibuat.");
    }

    public function update(Request $request, User $pengguna)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username,' . $pengguna->id,
            'nip' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100|unique:users,email,' . $pengguna->id,
            'password' => 'nullable|string|min:6',
            'peran' => 'required|in:SuperAdmin,Operator',
            'unit_pengolah_id' => 'nullable|exists:unit_pengolah,id',
            'aktif' => 'required|boolean',
        ]);

        if ($redirect = $this->validateOperatorUnit($validated)) {
            return $redirect;
        }

        $updateData = [
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'nip' => $validated['nip'] ?? null,
            'email' => $validated['email'] ?? null,
            'peran' => $validated['peran'],
            'unit_pengolah_id' => $validated['unit_pengolah_id'] ?? null,
            'aktif' => $validated['aktif'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $pengguna->update($updateData);

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Perbarui Akun Petugas',
            'entitas' => 'User',
            'entitas_id' => $pengguna->id,
            'detail' => ['username' => $pengguna->username],
        ]);

        return redirect()->route('pengguna.index')->with('sukses', "Data akun {$pengguna->name} berhasil diperbarui.");
    }

    public function destroy(User $pengguna)
    {
        $this->checkAccess();

        if ($pengguna->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        $nama = $pengguna->name;
        $pengguna->update(['aktif' => false]);

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Nonaktifkan Akun Petugas',
            'entitas' => 'User',
            'entitas_id' => $pengguna->id,
            'detail' => ['name' => $nama],
        ]);

        return redirect()->route('pengguna.index')->with('sukses', "Akun {$nama} telah dinonaktifkan.");
    }

    public function processResetRequest(Request $request, PermintaanResetPassword $permintaan)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'aksi' => 'required|in:reset,tolak',
            'password_baru' => 'required_if:aksi,reset|nullable|string|min:6',
        ]);

        if ($validated['aksi'] === 'reset') {
            $permintaan->user->update([
                'password' => Hash::make($validated['password_baru']),
            ]);
            $permintaan->update([
                'status' => 'Selesai',
                'diproses_oleh' => Auth::id(),
                'diproses_pada' => now(),
            ]);

            LogAudit::create([
                'user_id' => Auth::id(),
                'aksi' => 'Setel Ulang Password Pengguna',
                'entitas' => 'PermintaanResetPassword',
                'entitas_id' => $permintaan->id,
                'detail' => ['target_username' => $permintaan->user->username],
            ]);

            return redirect()->route('pengguna.index')->with('sukses', "Password akun {$permintaan->user->name} ({$permintaan->user->username}) berhasil disetel ulang.");
        }

        $permintaan->update([
            'status' => 'Ditolak',
            'diproses_oleh' => Auth::id(),
            'diproses_pada' => now(),
        ]);

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Tolak Permohonan Reset Password',
            'entitas' => 'PermintaanResetPassword',
            'entitas_id' => $permintaan->id,
            'detail' => ['target_username' => $permintaan->user->username],
        ]);

        return redirect()->route('pengguna.index')->with('sukses', "Permohonan reset password untuk {$permintaan->user->username} ditolak.");
    }
}
