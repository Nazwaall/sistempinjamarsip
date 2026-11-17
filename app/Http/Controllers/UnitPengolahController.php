<?php

namespace App\Http\Controllers;

use App\Models\LogAudit;
use App\Models\UnitPengolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitPengolahController extends Controller
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

        $units = UnitPengolah::withCount(['users', 'arsip', 'suratPeminjaman'])
            ->orderBy('id', 'asc')
            ->get();

        return view('unit_pengolah.index', compact('units'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:unit_pengolah,kode',
            'nama' => 'required|string|max:150',
            'kepala_nama' => 'required|string|max:150',
            'kepala_nip' => 'nullable|string|max:30',
            'lama_pinjam_standar' => 'required|integer|min:1|max:30',
            'lama_pinjam_maks' => 'required|integer|min:1|max:30',
        ]);

        $validated['kode'] = strtoupper($validated['kode']);
        $validated['aktif'] = true;

        $unit = UnitPengolah::create($validated);

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Tambah Unit Pengolah',
            'entitas' => 'UnitPengolah',
            'entitas_id' => $unit->id,
            'detail' => ['kode' => $unit->kode, 'nama' => $unit->nama],
        ]);

        return redirect()->route('unit-pengolah.index')->with('sukses', "Unit Pengolah {$unit->kode} ({$unit->nama}) berhasil ditambahkan.");
    }

    public function update(Request $request, UnitPengolah $unitPengolah)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:unit_pengolah,kode,' . $unitPengolah->id,
            'nama' => 'required|string|max:150',
            'kepala_nama' => 'required|string|max:150',
            'kepala_nip' => 'nullable|string|max:30',
            'lama_pinjam_standar' => 'required|integer|min:1|max:30',
            'lama_pinjam_maks' => 'required|integer|min:1|max:30',
            'aktif' => 'required|boolean',
        ]);

        $validated['kode'] = strtoupper($validated['kode']);

        $unitPengolah->update($validated);

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Perbarui Unit Pengolah',
            'entitas' => 'UnitPengolah',
            'entitas_id' => $unitPengolah->id,
            'detail' => ['kode' => $unitPengolah->kode],
        ]);

        return redirect()->route('unit-pengolah.index')->with('sukses', "Unit Pengolah {$unitPengolah->kode} berhasil diperbarui.");
    }

    public function destroy(UnitPengolah $unitPengolah)
    {
        $this->checkAccess();

        if ($unitPengolah->arsip()->exists() || $unitPengolah->suratPeminjaman()->exists()) {
            $unitPengolah->update(['aktif' => false]);
            return redirect()->route('unit-pengolah.index')->with('sukses', "Unit {$unitPengolah->kode} dinonaktifkan (memiliki riwayat data).");
        }

        $kode = $unitPengolah->kode;
        $unitPengolah->delete();

        return redirect()->route('unit-pengolah.index')->with('sukses', "Unit {$kode} berhasil dihapus.");
    }
}
