<?php

namespace App\Http\Controllers;

use App\Models\BuktiFoto;
use App\Models\LogAudit;
use App\Models\SuratPeminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PemindaiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedSuratId = $request->query('surat_id');
        $query = SuratPeminjaman::with('peminjam')
            ->whereIn('status', ['Menunggu Persetujuan', 'Janjian', 'Aktif', 'Terlambat'])
            ->latest('id');

        if ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
            $query->where(function ($sub) use ($user) {
                $sub->where('unit_pengolah_id', $user->unit_pengolah_id)
                    ->orWhere('petugas_id', $user->id);
            });
        }

        $daftarSurat = $query->take(30)->get();

        if ($selectedSuratId && !$daftarSurat->contains('id', (int) $selectedSuratId)) {
            $suratKhusus = SuratPeminjaman::with('peminjam')->find($selectedSuratId);
            if ($suratKhusus && $suratKhusus->dapatDiaksesOleh($user)) {
                $daftarSurat->prepend($suratKhusus);
            }
        }

        return view('pemindai.index', compact('daftarSurat', 'selectedSuratId'));
    }

    public function simpanBukti(Request $request)
    {
        $request->validate([
            'surat_id' => 'required|exists:surat_peminjaman,id',
            'scanned_image_data' => 'required|string',
            'jenis' => 'nullable|in:Identitas,Surat Bertanda Tangan,Serah Terima,Pengembalian,Scan Dokumen (CV)',
            'sumber' => 'nullable|string|max:20',
        ]);

        $surat = SuratPeminjaman::findOrFail($request->surat_id);
        $user = Auth::user();

        if (!$surat->dapatDiaksesOleh($user)) {
            abort(403, 'Anda tidak memiliki akses pada surat peminjaman bidang lain.');
        }

        $dataUri = $request->scanned_image_data;
        if (!str_contains($dataUri, 'base64,')) {
            return back()->with('error', 'Format gambar hasil pindai tidak valid.');
        }

        $encoded = substr($dataUri, strpos($dataUri, ',') + 1);
        $decoded = base64_decode($encoded);
        if ($decoded === false || strlen($decoded) < 100) {
            return back()->with('error', 'Data gambar hasil pindai terlalu kecil atau rusak.');
        }

        $filename = 'scan_dokumen_' . Str::uuid() . '.jpg';
        Storage::disk('public')->put("bukti/{$filename}", $decoded);

        BuktiFoto::create([
            'surat_id' => $surat->id,
            'jenis' => $request->input('jenis', 'Identitas'),
            'sumber' => $request->input('sumber', 'Kamera CV'),
            'file_path' => "bukti/{$filename}",
            'diunggah_oleh' => $user->id,
        ]);

        LogAudit::create([
            'user_id' => $user->id,
            'aksi' => 'Pindai Dokumen Cerdas (OpenCV)',
            'entitas' => 'SuratPeminjaman',
            'entitas_id' => $surat->id,
            'detail' => [
                'nomor_surat' => $surat->nomor_surat,
                'file_path' => "bukti/{$filename}",
            ],
        ]);

        return redirect()
            ->route('peminjaman.show', $surat->id)
            ->with('sukses', "Hasil scan dokumen (OpenCV) berhasil dilampirkan ke surat {$surat->nomor_surat}.");
    }
}
