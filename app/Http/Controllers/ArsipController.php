<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\ImporArsip;
use App\Models\LogAudit;
use App\Models\UnitPengolah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Arsip::with('unitPengolah')->where('aktif', true);

        // Batasi bidang bila bukan Super Admin
        if ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
            $query->where('unit_pengolah_id', $user->unit_pengolah_id);
        } elseif ($request->filled('unit_pengolah_id')) {
            $query->where('unit_pengolah_id', $request->unit_pengolah_id);
        }

        // Filter kata kunci
        if ($request->filled('q')) {
            $q = $request->q;
            $like = $this->likeOperator();
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('uraian_berkas', $like, "%{$q}%")
                    ->orWhere('uraian_informasi', $like, "%{$q}%")
                    ->orWhere('kode_klasifikasi', $like, "%{$q}%")
                    ->orWhere('kode_internal', $like, "%{$q}%")
                    ->orWhere('unit_pengolah_asal', $like, "%{$q}%")
                    ->orWhere('lokasi_simpan', $like, "%{$q}%");
            });
        }

        // Filter tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        // Filter bulan
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        // Filter status ketersediaan
        if ($request->filled('status')) {
            $query->where('status_ketersediaan', $request->status);
        }

        $arsipList = $query->orderBy('tahun', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $unitList = UnitPengolah::where('aktif', true)->get();
        $tahunList = Arsip::where('aktif', true)->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('arsip.index', compact('arsipList', 'unitList', 'tahunList'));
    }

    public function create()
    {
        $user = Auth::user();
        $units = $user->isSuperAdmin()
            ? UnitPengolah::where('aktif', true)->get()
            : UnitPengolah::where('id', $user->unit_pengolah_id)->get();

        return view('arsip.create', compact('units'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $unitId = $user->isSuperAdmin() ? $request->unit_pengolah_id : $user->unit_pengolah_id;

        $validated = $request->validate([
            'unit_pengolah_id' => $user->isSuperAdmin() ? 'required|exists:unit_pengolah,id' : 'nullable',
            'unit_pengolah_asal' => 'nullable|string|max:150',
            'uraian_berkas' => 'required|string',
            'tahun' => 'required|integer|min:1900|max:2100',
            'kode_klasifikasi' => 'nullable|string|max:100',
            'uraian_informasi' => 'nullable|string',
            'tanggal_surat' => 'nullable|date',
            'tingkat_perkembangan' => 'nullable|string|max:255',
            'jra_aktif' => 'nullable|integer',
            'jra_inaktif' => 'nullable|integer',
            'jra_keterangan' => 'nullable|string|max:100',
            'lokasi_simpan' => 'nullable|string|max:255',
            'klasifikasi_keamanan' => 'required|string|in:Biasa/Terbuka,Terbatas,Rahasia,Sangat Rahasia',
        ]);

        $unit = UnitPengolah::findOrFail($unitId);

        // Ambil bulan dari tanggal surat
        $bulan = null;
        if (!empty($validated['tanggal_surat'])) {
            $bulan = (int) Carbon::parse($validated['tanggal_surat'])->format('n');
        }

        // Buat kode internal unik: KODE-TAHUN-BULAN-URUT
        $bulanStr = $bulan ? str_pad($bulan, 2, '0', STR_PAD_LEFT) : '00';
        $countThisYear = Arsip::where('unit_pengolah_id', $unit->id)
            ->where('tahun', $validated['tahun'])
            ->count() + 1;
        $urutStr = str_pad($countThisYear, 4, '0', STR_PAD_LEFT);
        $kodeInternal = "{$unit->kode}-{$validated['tahun']}-{$bulanStr}-{$urutStr}";

        // Teks label otomatis untuk OCR matching
        $teksLabel = trim("{$validated['uraian_berkas']} " . ($bulan ? $this->getNamaBulan($bulan) : '') . " {$validated['tahun']}");

        Arsip::create([
            'unit_pengolah_id' => $unit->id,
            'unit_pengolah_asal' => trim((string) ($validated['unit_pengolah_asal'] ?? '')) ?: $unit->nama,
            'kode_internal' => $kodeInternal,
            'kode_klasifikasi' => $validated['kode_klasifikasi'] ?? null,
            'uraian_berkas' => $validated['uraian_berkas'],
            'uraian_informasi' => $validated['uraian_informasi'] ?? null,
            'tanggal_surat' => $validated['tanggal_surat'] ?? null,
            'tahun' => $validated['tahun'],
            'bulan' => $bulan,
            'tingkat_perkembangan' => ($validated['tingkat_perkembangan'] ?? '') ?: 'Asli',
            'jra_aktif' => $validated['jra_aktif'] ?? null,
            'jra_inaktif' => $validated['jra_inaktif'] ?? null,
            'jra_keterangan' => $validated['jra_keterangan'] ?? 'Permanen',
            'lokasi_simpan' => $validated['lokasi_simpan'] ?? null,
            'teks_label' => $teksLabel,
            'klasifikasi_keamanan' => $validated['klasifikasi_keamanan'],
            'status_ketersediaan' => 'Tersedia',
            'aktif' => true,
        ]);

        return redirect()->route('arsip.index')->with('sukses', "Berkas arsip {$kodeInternal} berhasil ditambahkan.");
    }

    public function show(Arsip $arsip)
    {
        $arsip->load([
            'unitPengolah',
            'suratPeminjamanItems' => function ($q) {
                $q->with(['surat.peminjam', 'surat.petugas'])->latest('id');
            }
        ]);

        return view('arsip.show', compact('arsip'));
    }

    public function edit(Arsip $arsip)
    {
        $user = Auth::user();
        if ($user->unit_pengolah_id && !$user->isSuperAdmin() && $user->unit_pengolah_id !== $arsip->unit_pengolah_id) {
            abort(403, 'Anda tidak berwenang menyunting arsip unit lain.');
        }

        $units = $user->isSuperAdmin() ? UnitPengolah::where('aktif', true)->get() : collect([$arsip->unitPengolah]);
        return view('arsip.edit', compact('arsip', 'units'));
    }

    public function update(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        if ($user->unit_pengolah_id && !$user->isSuperAdmin() && $user->unit_pengolah_id !== $arsip->unit_pengolah_id) {
            abort(403);
        }

        $validated = $request->validate([
            'unit_pengolah_asal' => 'nullable|string|max:150',
            'uraian_berkas' => 'required|string',
            'tahun' => 'required|integer|min:1900|max:2100',
            'kode_klasifikasi' => 'nullable|string|max:100',
            'uraian_informasi' => 'nullable|string',
            'tanggal_surat' => 'nullable|date',
            'tingkat_perkembangan' => 'nullable|string|max:255',
            'lokasi_simpan' => 'nullable|string|max:255',
            'klasifikasi_keamanan' => 'required|string',
            'status_ketersediaan' => 'required|string|in:Tersedia,Dipinjam,Hilang,Rusak',
        ]);

        if (empty(trim((string) ($validated['unit_pengolah_asal'] ?? '')))) {
            $validated['unit_pengolah_asal'] = $arsip->unitPengolah?->nama;
        }
        if (empty($validated['tingkat_perkembangan'])) {
            $validated['tingkat_perkembangan'] = $arsip->tingkat_perkembangan ?: 'Asli';
        }

        $bulan = $arsip->bulan;
        if (!empty($validated['tanggal_surat'])) {
            $bulan = (int) Carbon::parse($validated['tanggal_surat'])->format('n');
        }

        $validated['bulan'] = $bulan;
        $validated['teks_label'] = trim("{$validated['uraian_berkas']} " . ($bulan ? $this->getNamaBulan($bulan) : '') . " {$validated['tahun']}");

        $arsip->update($validated);

        return redirect()->route('arsip.index')->with('sukses', "Data arsip {$arsip->kode_internal} berhasil diperbarui.");
    }

    public function nilaiKondisi(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        if ($user->unit_pengolah_id && !$user->isSuperAdmin() && $user->unit_pengolah_id !== $arsip->unit_pengolah_id) {
            abort(403, 'Anda tidak berwenang menilai arsip unit lain.');
        }

        $validated = $request->validate([
            'skor_integritas' => 'required|integer|min:0|max:100',
            'ringkasan_kerusakan' => 'required|string|max:255',
            'kondisi_disarankan' => 'nullable|in:Tersedia,Rusak',
            'foto_kondisi_data' => 'nullable|string',
        ]);

        $fotoPath = $this->saveBase64Image($request->input('foto_kondisi_data'), 'kondisi_arsip', 'retensi');

        $updateData = [
            'skor_integritas_terakhir' => (int) $validated['skor_integritas'],
            'catatan_kondisi_terakhir' => mb_substr($validated['ringkasan_kerusakan'], 0, 255),
            'terakhir_diperiksa_pada' => now(),
        ];

        if ($arsip->status_ketersediaan !== 'Dipinjam' && !empty($validated['kondisi_disarankan'])) {
            $updateData['status_ketersediaan'] = $validated['kondisi_disarankan'];
        }

        $arsip->update($updateData);

        LogAudit::create([
            'user_id' => $user->id,
            'aksi' => 'Penilaian Kondisi & Retensi (CNN)',
            'entitas' => 'Arsip',
            'entitas_id' => $arsip->id,
            'detail' => [
                'kode_internal' => $arsip->kode_internal,
                'skor_integritas' => (int) $validated['skor_integritas'],
                'ringkasan' => $validated['ringkasan_kerusakan'],
                'foto_path' => $fotoPath,
            ],
        ]);

        return redirect()->route('arsip.show', $arsip)->with('sukses', "Hasil penilaian CNN tersimpan: {$validated['ringkasan_kerusakan']}");
    }

    public function destroy(Arsip $arsip)
    {
        $user = Auth::user();
        if ($user->unit_pengolah_id && !$user->isSuperAdmin() && $user->unit_pengolah_id !== $arsip->unit_pengolah_id) {
            abort(403);
        }

        if ($arsip->status_ketersediaan === 'Dipinjam') {
            return back()->with('error', 'Arsip yang sedang dipinjam tidak dapat dihapus.');
        }

        // Jika arsip pernah terikat pada transaksi surat peminjaman, nonaktifkan agar arsip tidak merusak riwayat surat
        if ($arsip->suratPeminjamanItems()->exists()) {
            $arsip->update(['aktif' => false]);
            return redirect()->route('arsip.index')->with('sukses', "Arsip {$arsip->kode_internal} memiliki riwayat peminjaman resmi, sehingga dinonaktifkan dari daftar.");
        }

        // Hapus permanen dari database Supabase jika belum pernah dipinjam
        $kode = $arsip->kode_internal;
        $arsip->delete();

        return redirect()->route('arsip.index')->with('sukses', "Arsip {$kode} berhasil dihapus permanen dari basis data.");
    }

    // Hapus Massal: Pilihan Berkas atau Semua Berkas Sekaligus
    public function bulkDestroy(Request $request)
    {
        $user = Auth::user();
        $aksi = $request->input('aksi'); // 'pilihan' atau 'semua'

        if ($aksi === 'semua') {
            $query = Arsip::query();
            if ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
                $query->where('unit_pengolah_id', $user->unit_pengolah_id);
            }

            // Lindungi arsip yang sedang dipinjam
            $query->where('status_ketersediaan', '!=', 'Dipinjam');

            // Cek arsip yang memiliki riwayat transaksi surat peminjaman
            $idDenganRiwayat = DB::table('surat_peminjaman_item')->pluck('arsip_id')->unique()->toArray();

            // Nonaktifkan arsip yang memiliki riwayat
            $nonaktifkanCount = 0;
            if (!empty($idDenganRiwayat)) {
                $nonaktifkanCount = (clone $query)->whereIn('id', $idDenganRiwayat)->update(['aktif' => false]);
            }

            // Hapus fisik permanen arsip yang belum pernah dipinjam
            $terhapusPermanen = (clone $query)->whereNotIn('id', $idDenganRiwayat)->delete();

            return redirect()->route('arsip.index')->with('sukses', "Berhasil menghapus {$terhapusPermanen} berkas arsip secara permanen dari basis data.");
        }

        // Hapus berkas yang dipilih melalui centang
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Pilih minimal satu berkas arsip untuk dihapus.');
        }

        $query = Arsip::whereIn('id', $ids);
        if ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
            $query->where('unit_pengolah_id', $user->unit_pengolah_id);
        }

        // Periksa apakah ada yang sedang berstatus dipinjam
        $sedangDipinjamCount = (clone $query)->where('status_ketersediaan', 'Dipinjam')->count();
        if ($sedangDipinjamCount > 0) {
            return back()->with('error', "Sebanyak {$sedangDipinjamCount} berkas yang Anda pilih sedang dalam status Dipinjam dan tidak dapat dihapus.");
        }

        $idDenganRiwayat = DB::table('surat_peminjaman_item')->whereIn('arsip_id', $ids)->pluck('arsip_id')->unique()->toArray();

        $nonaktifCount = 0;
        if (!empty($idDenganRiwayat)) {
            $nonaktifCount = (clone $query)->whereIn('id', $idDenganRiwayat)->update(['aktif' => false]);
        }

        $terhapusPermanen = (clone $query)->whereNotIn('id', $idDenganRiwayat)->delete();

        return redirect()->route('arsip.index')->with('sukses', "Berhasil menghapus {$terhapusPermanen} berkas arsip terpilih secara permanen.");
    }

    // Endpoint API untuk pencocokan hasil OCR label berkas (FR-09, FR-35)
    public function cariLabel(Request $request)
    {
        $user = Auth::user();
        $unitId = $user->unit_pengolah_id;

        $teks = trim($request->input('teks', ''));
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $query = Arsip::where('status_ketersediaan', 'Tersedia')->where('aktif', true);
        if ($unitId && !$user->isSuperAdmin()) {
            $query->where('unit_pengolah_id', $unitId);
        }

        // Pencarian cerdas dengan pg_trgm (PostgreSQL) atau LIKE (MySQL)
        $like = $this->likeOperator();
        if (DB::getDriverName() === 'pgsql' && !empty($teks)) {
            $arsip = $query->select('arsip.*')
                ->selectRaw("
                    (CASE WHEN tahun = ? THEN 3 ELSE 0 END) +
                    (CASE WHEN bulan = ? THEN 3 ELSE 0 END) +
                    GREATEST(similarity(COALESCE(teks_label, ''), ?), similarity(uraian_berkas, ?)) AS skor
                ", [$tahun ?: 0, $bulan ?: 0, $teks, $teks])
                ->orderByDesc('skor')
                ->limit(5)
                ->get();
        } else {
            if ($tahun) $query->where('tahun', $tahun);
            if ($bulan) $query->where('bulan', $bulan);
            if ($teks) {
                $query->where(function ($q) use ($teks, $like) {
                    $q->where('uraian_berkas', $like, "%{$teks}%")
                      ->orWhere('teks_label', $like, "%{$teks}%")
                      ->orWhere('kode_internal', $like, "%{$teks}%");
                });
            }
            $arsip = $query->limit(5)->get();
        }

        return response()->json($arsip);
    }

    // Tampilan Impor Excel
    public function showImport()
    {
        return view('arsip.import');
    }

    // Proses Unggah & Impor Excel (FR-33)
    public function processImport(Request $request)
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $request->validate([
            'file_arsip' => 'required|file|mimes:xlsx,xls,csv|max:20480', // Maks 20 MB
            'duplikat' => 'required|in:semua,lewati,perbarui',
        ]);

        $user = Auth::user();
        $file = $request->file('file_arsip');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheets = $spreadsheet->getAllSheets();

            $unitDefault = $user->unitPengolah ?: UnitPengolah::where('kode', 'ARS')->first();
            $berhasil = 0;
            $gagal = 0;
            $totalBarisDitemukan = 0;

            // 1. Cache hitungan tahun di memori untuk mencegah query count berulang ke Supabase
            $yearCounters = [];

            // 2. Pre-fetch existing data jika mode lewati atau perbarui dipilih
            $existingMap = [];
            if ($request->duplikat !== 'semua') {
                $existingItems = Arsip::where('unit_pengolah_id', $unitDefault->id)
                    ->select('id', 'uraian_berkas', 'tahun', 'uraian_informasi')
                    ->get();
                foreach ($existingItems as $ex) {
                    $key = strtolower(trim($ex->uraian_berkas)) . '|' . $ex->tahun . '|' . strtolower(trim($ex->uraian_informasi ?? ''));
                    $existingMap[$key] = $ex->id;
                }
            }

            $insertBatch = [];
            $updateBatch = [];
            $now = now();

            foreach ($sheets as $sheet) {
                $rows = $sheet->toArray(null, true, true, true);
                if (empty($rows)) continue;

                // Cari baris header pada sheet ini
                $headerRowIndex = null;
                $colMap = [];

                foreach ($rows as $rowIndex => $row) {
                    $score = 0;
                    $tempMap = [];

                    foreach ($row as $colKey => $cellValue) {
                        $raw = strtolower(trim((string)$cellValue));
                        $cleaned = preg_replace('/[^a-z0-9]/', '', $raw);
                        if (empty($cleaned)) continue;

                        if (str_contains($cleaned, 'uraianberkas') || str_contains($cleaned, 'namaberkas') || str_contains($cleaned, 'judulberkas') || str_contains($cleaned, 'tentang') || str_contains($cleaned, 'perihal') || str_contains($cleaned, 'isiberkas') || str_contains($cleaned, 'uraianmasalah')) {
                            $tempMap['uraian_berkas'] = $colKey;
                            $score += 3;
                        } elseif (str_contains($cleaned, 'uraianinformasi') || str_contains($cleaned, 'informasiarsip') || str_contains($cleaned, 'isiinformasi') || str_contains($cleaned, 'rincian')) {
                            $tempMap['uraian_informasi'] = $colKey;
                            $score += 2;
                        } elseif (str_contains($cleaned, 'uraian') && !isset($tempMap['uraian_berkas'])) {
                            $tempMap['uraian_berkas'] = $colKey;
                            $score += 2;
                        } elseif (str_contains($cleaned, 'kodeklasifikasi') || str_contains($cleaned, 'klasifikasi') || str_contains($cleaned, 'kodeberkas') || str_contains($cleaned, 'nomorberkas') || str_contains($cleaned, 'noberkas')) {
                            $tempMap['kode_klasifikasi'] = $colKey;
                            $score += 2;
                        } elseif (str_contains($cleaned, 'unitpengolah') || str_contains($cleaned, 'bidangasal') || str_contains($cleaned, 'skpd')) {
                            $tempMap['unit_pengolah'] = $colKey;
                            $score += 1;
                        } elseif (str_contains($cleaned, 'tanggalsurat') || str_contains($cleaned, 'tglsurat') || str_contains($cleaned, 'tanggal') || str_contains($cleaned, 'waktu')) {
                            $tempMap['tanggal_surat'] = $colKey;
                            $score += 1;
                        } elseif (str_contains($cleaned, 'tahun') || str_contains($cleaned, 'thn') || str_contains($cleaned, 'kurunwaktu')) {
                            $tempMap['tahun'] = $colKey;
                            $score += 2;
                        } elseif (str_contains($cleaned, 'perkembangan') || str_contains($cleaned, 'keaslian') || str_contains($cleaned, 'kondisi')) {
                            $tempMap['tingkat_perkembangan'] = $colKey;
                            $score += 1;
                        } elseif (str_contains($cleaned, 'lokasisimpan') || str_contains($cleaned, 'lokasi') || str_contains($cleaned, 'boks') || str_contains($cleaned, 'box') || str_contains($cleaned, 'rak') || str_contains($cleaned, 'ruang')) {
                            $tempMap['lokasi_simpan'] = $colKey;
                            $score += 1;
                        }
                    }

                    // Baris header valid jika memiliki skor >= 3 (misal ada kolom uraian/berkas + tahun atau nomor)
                    if ($score >= 3 && isset($tempMap['uraian_berkas'])) {
                        $headerRowIndex = $rowIndex;
                        $colMap = $tempMap;
                        break;
                    }
                }

                if (!$headerRowIndex || empty($colMap['uraian_berkas'])) {
                    continue; // Lewati sheet jika tidak memiliki kolom berkas
                }

                $lastUraianBerkas = '';

                foreach ($rows as $rowIndex => $row) {
                    if ($rowIndex <= $headerRowIndex) continue;

                    $rawUraianBerkas = trim((string)($row[$colMap['uraian_berkas']] ?? ''));
                    $rawUraianInfo = isset($colMap['uraian_informasi']) ? trim((string)($row[$colMap['uraian_informasi']] ?? '')) : '';

                    // Jika uraian berkas kosong tapi ada uraian informasi atau ada berkas sebelumnya (pola merged cells)
                    if (empty($rawUraianBerkas)) {
                        if (!empty($rawUraianInfo)) {
                            $uraianBerkas = $lastUraianBerkas ?: $rawUraianInfo;
                        } else {
                            continue; // Lewati baris yang benar-benar kosong
                        }
                    } else {
                        $uraianBerkas = $rawUraianBerkas;
                        $lastUraianBerkas = $rawUraianBerkas;
                    }

                    $totalBarisDitemukan++;

                    // Ekstraksi Tanggal & Tahun
                    $tahunRaw = isset($colMap['tahun']) ? trim((string)($row[$colMap['tahun']] ?? '')) : '';
                    $tglSuratRaw = isset($colMap['tanggal_surat']) ? trim((string)($row[$colMap['tanggal_surat']] ?? '')) : '';

                    $tanggalSurat = null;
                    $tahun = null;
                    $bulan = null;

                    // Cek jika tglSuratRaw berupa angka serial Excel
                    if (is_numeric($tglSuratRaw) && $tglSuratRaw > 10000 && $tglSuratRaw < 60000) {
                        try {
                            $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tglSuratRaw);
                            $tanggalSurat = $dateObj->format('Y-m-d');
                            $bulan = (int) $dateObj->format('n');
                            $tahun = (int) $dateObj->format('Y');
                        } catch (\Exception $e) {}
                    } elseif (!empty($tglSuratRaw)) {
                        try {
                            $parsed = Carbon::parse($tglSuratRaw);
                            $tanggalSurat = $parsed->toDateString();
                            $bulan = (int) $parsed->format('n');
                            $tahun = (int) $parsed->format('Y');
                        } catch (\Exception $e) {}
                    }

                    // Cari tahun 4 digit dari tahunRaw jika belum didapat
                    if (!$tahun && !empty($tahunRaw)) {
                        if (preg_match('/\b(19\d{2}|20\d{2})\b/', $tahunRaw, $matches)) {
                            $tahun = (int) $matches[1];
                        }
                    }

                    if (!$tahun) $tahun = (int) date('Y');

                    $kodeKlasifikasi = isset($colMap['kode_klasifikasi']) ? substr(trim((string)($row[$colMap['kode_klasifikasi']] ?? '')), 0, 100) : null;
                    $lokasiSimpan = isset($colMap['lokasi_simpan']) ? substr(trim((string)($row[$colMap['lokasi_simpan']] ?? '')), 0, 255) : null;
                    $tingkat = isset($colMap['tingkat_perkembangan']) ? substr(trim((string)($row[$colMap['tingkat_perkembangan']] ?? 'Asli')), 0, 255) : 'Asli';
                    $unitAsalRaw = isset($colMap['unit_pengolah']) ? substr(trim((string)($row[$colMap['unit_pengolah']] ?? '')), 0, 150) : '';
                    $unitAsal = $unitAsalRaw !== '' ? $unitAsalRaw : $unitDefault->nama;

                    // Cek duplikasi in-memory (0 latency)
                    $dupKey = strtolower(trim($uraianBerkas)) . '|' . $tahun . '|' . strtolower(trim($rawUraianInfo));

                    if ($request->duplikat === 'lewati' && isset($existingMap[$dupKey])) {
                        $gagal++;
                        continue;
                    }

                    if ($request->duplikat === 'perbarui' && isset($existingMap[$dupKey])) {
                        $existingId = $existingMap[$dupKey];
                        $updateBatch[] = [
                            'id' => $existingId,
                            'unit_pengolah_asal' => $unitAsal,
                            'kode_klasifikasi' => $kodeKlasifikasi,
                            'lokasi_simpan' => $lokasiSimpan,
                            'tingkat_perkembangan' => $tingkat,
                            'tanggal_surat' => $tanggalSurat,
                            'bulan' => $bulan,
                            'teks_label' => trim("{$uraianBerkas} " . ($bulan ? $this->getNamaBulan($bulan) : '') . " {$tahun}"),
                            'updated_at' => $now,
                        ];
                        $berhasil++;
                        continue;
                    }

                    // Ambil urutan tahun dari cache in-memory (0 latency)
                    if (!isset($yearCounters[$tahun])) {
                        $yearCounters[$tahun] = Arsip::where('unit_pengolah_id', $unitDefault->id)->where('tahun', $tahun)->count();
                    }
                    $yearCounters[$tahun]++;
                    $nomorUrut = $yearCounters[$tahun];

                    $bulanStr = $bulan ? str_pad($bulan, 2, '0', STR_PAD_LEFT) : '00';
                    $kodeInternal = "{$unitDefault->kode}-{$tahun}-{$bulanStr}-" . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT);
                    $teksLabel = trim("{$uraianBerkas} " . ($bulan ? $this->getNamaBulan($bulan) : '') . " {$tahun}");

                    $insertBatch[] = [
                        'unit_pengolah_id' => $unitDefault->id,
                        'unit_pengolah_asal' => $unitAsal,
                        'kode_internal' => $kodeInternal,
                        'kode_klasifikasi' => $kodeKlasifikasi,
                        'uraian_berkas' => $uraianBerkas,
                        'uraian_informasi' => $rawUraianInfo ?: null,
                        'tanggal_surat' => $tanggalSurat,
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                        'tingkat_perkembangan' => $tingkat,
                        'lokasi_simpan' => $lokasiSimpan,
                        'teks_label' => $teksLabel,
                        'klasifikasi_keamanan' => 'Biasa/Terbuka',
                        'status_ketersediaan' => 'Tersedia',
                        'aktif' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $existingMap[$dupKey] = true;
                    $berhasil++;
                }
            }

            if ($totalBarisDitemukan === 0) {
                return back()->with('error', 'Tidak ditemukan data arsip yang valid pada berkas Excel tersebut. Pastikan berkas memiliki kolom "Uraian Berkas" atau "Nama Berkas".');
            }

            // Eksekusi Batch Insert ke Supabase (100 baris per query, cepat dan hemat kuota TCP round-trip)
            DB::beginTransaction();
            try {
                if (!empty($insertBatch)) {
                    foreach (array_chunk($insertBatch, 100) as $chunk) {
                        DB::table('arsip')->insert($chunk);
                    }
                }

                if (!empty($updateBatch)) {
                    foreach ($updateBatch as $up) {
                        $id = $up['id'];
                        unset($up['id']);
                        DB::table('arsip')->where('id', $id)->update($up);
                    }
                }

                ImporArsip::create([
                    'unit_pengolah_id' => $unitDefault->id,
                    'nama_file' => $file->getClientOriginalName(),
                    'jumlah_baris' => $berhasil + $gagal,
                    'jumlah_berhasil' => $berhasil,
                    'jumlah_gagal' => $gagal,
                    'dibuat_oleh' => $user->id,
                ]);

                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }

            return redirect()->route('arsip.index')->with('sukses', "Impor berhasil! {$berhasil} berkas tersimpan ({$gagal} dilewati dari total {$totalBarisDitemukan} baris arsip).");
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan membaca berkas: ' . $e->getMessage());
        }
    }

    private function getNamaBulan(int $bulan): string
    {
        $nama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $nama[$bulan] ?? '';
    }
}
