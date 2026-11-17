<?php

namespace App\Http\Controllers;

use App\Models\LogAudit;
use App\Models\Pengaturan;
use App\Models\UnitPengolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengaturanController extends Controller
{
    private function checkAccess(): void
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Akses terbatas untuk Super Admin.');
        }
    }

    public function index()
    {
        $this->checkAccess();

        $settings = Pengaturan::pluck('nilai', 'kunci')->toArray();
        $units = UnitPengolah::where('aktif', true)->orderBy('id')->get();
        return view('pengaturan.index', compact('settings', 'units'));
    }

    public function update(Request $request)
    {
        $this->checkAccess();
        $validated = $request->validate([
            'nama_dinas' => 'required|string|max:150',
            'kop_baris_1' => 'required|string|max:150',
            'kop_baris_2' => 'required|string|max:150',
            'alamat_dinas' => 'required|string|max:255',
            'telepon_dinas' => 'nullable|string|max:50',
            'email_dinas' => 'nullable|email|max:100',
            'website_dinas' => 'nullable|string|max:100',
            'lama_pinjam_default' => 'required|integer|min:1|max:30',
            'units' => 'nullable|array',
            'units.*.kepala_nama' => 'required|string|max:150',
            'units.*.kepala_nip' => 'nullable|string|max:30',
            'units.*.lama_pinjam_standar' => 'required|integer|min:1|max:30',
            'units.*.lama_pinjam_maks' => 'required|integer|min:1|max:30',
        ]);

        $unitInputs = $validated['units'] ?? [];
        unset($validated['units']);

        foreach ($validated as $key => $value) {
            Pengaturan::updateOrCreate(
                ['kunci' => $key],
                ['nilai' => $value]
            );
        }

        foreach ($unitInputs as $unitId => $unitData) {
            UnitPengolah::where('id', $unitId)->update([
                'kepala_nama' => $unitData['kepala_nama'],
                'kepala_nip' => $unitData['kepala_nip'] ?? null,
                'lama_pinjam_standar' => (int) $unitData['lama_pinjam_standar'],
                'lama_pinjam_maks' => (int) $unitData['lama_pinjam_maks'],
            ]);
        }

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Perbarui Pengaturan Sistem',
            'entitas' => 'Pengaturan',
            'entitas_id' => null,
            'detail' => $validated,
        ]);

        return redirect()->route('pengaturan.index')->with('sukses', 'Pengaturan sistem, kop surat, dan parameter bidang berhasil diperbarui.');
    }
}
