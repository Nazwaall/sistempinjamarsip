<?php

namespace App\Http\Controllers;

use App\Models\LogAudit;
use App\Models\Peminjam;
use App\Models\Pengaturan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PeminjamController extends Controller
{
    public function index(Request $request)
    {
        $peminjamList = $this->buildFilteredQuery($request)
            ->orderBy('nama', 'asc')
            ->paginate(15)
            ->withQueryString();

        $countsByJenis = Peminjam::selectRaw('jenis, COUNT(*) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis')
            ->toArray();

        $statsPeminjam = [
            'total' => array_sum($countsByJenis),
            'internal' => $countsByJenis['Internal'] ?? 0,
            'skpd' => $countsByJenis['SKPD'] ?? 0,
            'kecamatan' => $countsByJenis['Kecamatan / Kelurahan'] ?? 0,
            'eksternal' => $countsByJenis['Eksternal'] ?? 0,
            'aktif' => Peminjam::whereHas('suratPeminjaman', fn($q) => $q->whereIn('status', ['Aktif', 'Terlambat']))->count(),
        ];

        return view('peminjam.index', compact('peminjamList', 'statsPeminjam'));
    }

    public function exportPdf(Request $request)
    {
        $peminjamList = $this->buildFilteredQuery($request)
            ->orderBy('nama', 'asc')
            ->get();

        $pengaturan = Pengaturan::pluck('nilai', 'kunci');
        $jenisDipilih = $request->filled('jenis')
            ? (Peminjam::JENIS_LABELS[$request->jenis] ?? $request->jenis)
            : 'Semua Kategori';
        $kataKunci = trim((string) $request->input('q', ''));
        $dicetakOleh = Auth::user()->name;

        $pdf = Pdf::loadView('peminjam.pdf', compact(
            'peminjamList',
            'pengaturan',
            'jenisDipilih',
            'kataKunci',
            'dicetakOleh'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('Daftar_Peminjam_Arsip.pdf');
    }

    protected function buildFilteredQuery(Request $request)
    {
        $query = Peminjam::withCount([
            'suratPeminjaman',
            'suratPeminjaman as pinjaman_aktif_count' => fn($q) => $q->whereIn('status', ['Aktif', 'Terlambat']),
        ]);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $like = $this->likeOperator();
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('nama', $like, "%{$q}%")
                    ->orWhere('nip_nik', $like, "%{$q}%")
                    ->orWhere('instansi_unit', $like, "%{$q}%")
                    ->orWhere('no_hp', $like, "%{$q}%")
                    ->orWhere('jaminan', $like, "%{$q}%");
            });
        }

        return $query;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'jenis' => ['required', Rule::in(array_keys(Peminjam::JENIS_LABELS))],
            'nip_nik' => 'nullable|string|max:30',
            'jabatan' => 'nullable|string|max:100',
            'instansi_unit' => 'required|string|max:150',
            'alamat' => $request->jenis === 'Eksternal' ? 'required|string|max:255' : 'nullable|string|max:255',
            'no_hp' => 'required|string|max:25',
            'email' => 'nullable|email|max:100',
            'jaminan_opsi' => 'nullable|string|max:100',
            'jaminan_lainnya' => 'nullable|string|max:100',
            'jaminan' => 'nullable|string|max:100',
        ]);

        $validated['jaminan'] = $this->resolveJaminan($request);
        $peminjam = Peminjam::create($validated);

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Tambah Data Peminjam',
            'entitas' => 'Peminjam',
            'entitas_id' => $peminjam->id,
            'detail' => ['nama' => $peminjam->nama, 'jenis' => $peminjam->jenis],
        ]);

        return redirect()->route('peminjam.index')->with('sukses', "Data peminjam {$peminjam->nama} berhasil disimpan.");
    }

    public function show(Peminjam $peminjam)
    {
        return view('peminjam.show', $this->loadShowData($peminjam));
    }

    public function exportShowPdf(Peminjam $peminjam)
    {
        $data = $this->loadShowData($peminjam);
        $data['pengaturan'] = Pengaturan::pluck('nilai', 'kunci');
        $data['dicetakOleh'] = Auth::user()->name;

        $pdf = Pdf::loadView('peminjam.show-pdf', $data)
            ->setPaper('a4', 'portrait');

        $slugNama = Str::slug($peminjam->nama, '_');
        return $pdf->download("Riwayat_Peminjam_{$slugNama}.pdf");
    }

    protected function loadShowData(Peminjam $peminjam): array
    {
        $peminjam->load([
            'suratPeminjaman' => function ($q) {
                $q->with(['unitPengolah', 'items.arsip'])->latest('id');
            }
        ]);

        $totalSurat = $peminjam->suratPeminjaman->count();
        $suratAktif = $peminjam->suratPeminjaman->whereIn('status', ['Aktif', 'Terlambat'])->count();
        $suratSelesai = $peminjam->suratPeminjaman->where('status', 'Selesai')->count();

        return compact('peminjam', 'totalSurat', 'suratAktif', 'suratSelesai');
    }

    public function update(Request $request, Peminjam $peminjam)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'jenis' => ['required', Rule::in(array_keys(Peminjam::JENIS_LABELS))],
            'nip_nik' => 'nullable|string|max:30',
            'jabatan' => 'nullable|string|max:100',
            'instansi_unit' => 'required|string|max:150',
            'alamat' => $request->jenis === 'Eksternal' ? 'required|string|max:255' : 'nullable|string|max:255',
            'no_hp' => 'required|string|max:25',
            'email' => 'nullable|email|max:100',
            'jaminan_opsi' => 'nullable|string|max:100',
            'jaminan_lainnya' => 'nullable|string|max:100',
            'jaminan' => 'nullable|string|max:100',
        ]);

        $validated['jaminan'] = $this->resolveJaminan($request);
        $peminjam->update($validated);

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Perbarui Data Peminjam',
            'entitas' => 'Peminjam',
            'entitas_id' => $peminjam->id,
            'detail' => ['nama' => $peminjam->nama],
        ]);

        return redirect()->route('peminjam.index')->with('sukses', "Data peminjam {$peminjam->nama} berhasil diperbarui.");
    }

    public function destroy(Peminjam $peminjam)
    {
        $adaPinjamanAktif = $peminjam->suratPeminjaman()->whereIn('status', ['Aktif', 'Terlambat'])->exists();
        if ($adaPinjamanAktif) {
            return back()->with('error', "Peminjam {$peminjam->nama} memiliki surat peminjaman aktif dan tidak dapat dihapus.");
        }

        $nama = $peminjam->nama;
        $peminjam->delete();

        LogAudit::create([
            'user_id' => Auth::id(),
            'aksi' => 'Hapus Data Peminjam',
            'entitas' => 'Peminjam',
            'entitas_id' => null,
            'detail' => ['nama' => $nama],
        ]);

        return redirect()->route('peminjam.index')->with('sukses', "Data peminjam {$nama} telah dihapus.");
    }
}
