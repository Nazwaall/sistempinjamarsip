<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\BuktiFoto;
use App\Models\LogAudit;
use App\Models\NomorSuratCounter;
use App\Models\Peminjam;
use App\Models\Pengaturan;
use App\Models\Perpanjangan;
use App\Models\SuratPeminjaman;
use App\Models\SuratPeminjamanItem;
use App\Models\UnitPengolah;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = SuratPeminjaman::with(['peminjam', 'unitPengolah', 'petugas', 'items.arsip']);

        if ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
            $query->where(function ($sub) use ($user) {
                $sub->where('unit_pengolah_id', $user->unit_pengolah_id)
                    ->orWhere('petugas_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bentuk_layanan')) {
            $query->where('bentuk_layanan', $request->bentuk_layanan);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $like = $this->likeOperator();
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('nomor_surat', $like, "%{$q}%")
                    ->orWhere('maksud_keperluan', $like, "%{$q}%")
                    ->orWhere('jaminan', $like, "%{$q}%")
                    ->orWhereHas('peminjam', function ($pq) use ($q, $like) {
                        $pq->where('nama', $like, "%{$q}%")
                           ->orWhere('instansi_unit', $like, "%{$q}%");
                    });
            });
        }

        $peminjamanList = $query->latest('id')->paginate(10)->withQueryString();
        return view('peminjaman.index', compact('peminjamanList'));
    }

    public function create()
    {
        $user = Auth::user();
        $unitId = $user->unit_pengolah_id;

        $arsipQuery = Arsip::with('unitPengolah')
            ->where('aktif', true)
            ->where('status_ketersediaan', '!=', 'Hilang');
        if ($unitId && !$user->isSuperAdmin()) {
            $arsipQuery->where('unit_pengolah_id', $unitId);
        }
        $arsipTersedia = $arsipQuery->orderBy('tahun', 'desc')->get();

        $peminjamList = Peminjam::orderBy('nama', 'asc')->get();
        $units = UnitPengolah::where('aktif', true)->orderBy('id')->get();
        $unit = $user->unitPengolah
            ?: ($units->firstWhere('kode', 'ARS') ?: $units->first());
        $tahunList = Arsip::where('aktif', true)->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $petugasList = User::with('unitPengolah')
            ->where('aktif', true)
            ->whereRaw('LOWER(name) != ?', ['super admin sipa'])
            ->orderBy('name')
            ->get();

        return view('peminjaman.create', compact('arsipTersedia', 'peminjamList', 'unit', 'units', 'tahunList', 'petugasList'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi
        $request->validate([
            'unit_pengolah_id' => 'nullable|exists:unit_pengolah,id',
            'arsip_ids' => 'required|array|min:1',
            'arsip_ids.*' => 'exists:arsip,id',
            'bentuk_layanan' => ['required', Rule::in(SuratPeminjaman::BENTUK_LAYANAN_LIST)],
            'tipe_peminjam' => 'required|in:terdaftar,baru',
            'tanggal_mulai' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_mulai',
            'maksud_keperluan' => 'required|string|min:5',
            'jaminan_opsi' => 'nullable|string|max:100',
            'jaminan_lainnya' => 'nullable|string|max:100',
            'jaminan' => 'nullable|string|max:100',
            'metode_ttd' => 'nullable|in:Digital,Manual',
            'petugas_layanan_nama' => 'nullable|string|max:150',
            'petugas_layanan_nip' => 'nullable|string|max:30',
            'petugas_layanan_bidang' => 'nullable|string|max:150',
        ]);

        $selectedUnitId = $request->input('unit_pengolah_id') ?: $user->unit_pengolah_id;
        $unit = ($selectedUnitId ? UnitPengolah::find($selectedUnitId) : null)
            ?: ($user->unitPengolah ?: UnitPengolah::where('kode', 'ARS')->firstOrFail());

        $petugasLayanan = $this->resolvePetugasLayanan($request, $user, $unit);
        if (isset($petugasLayanan['error'])) {
            return back()->withErrors(['petugas_layanan_nama' => $petugasLayanan['error']])->withInput();
        }

        $jaminanInput = $this->resolveJaminan($request);
        $bentukLayanan = $request->input('bentuk_layanan', 'Fisik');

        // Cek batas maksimum peminjaman
        $tglMulai = Carbon::parse($request->tanggal_mulai);
        $tglKembali = Carbon::parse($request->tanggal_kembali);
        $durasiHari = $tglMulai->diffInDays($tglKembali) + 1;

        if ($durasiHari > $unit->lama_pinjam_maks) {
            return back()->withErrors(['tanggal_kembali' => "Lama pinjam tidak boleh melebihi {$unit->lama_pinjam_maks} hari."])->withInput();
        }

        // Cek kelayakan & ketersediaan arsip sesuai bentuk layanan
        $arsipTerpilih = Arsip::whereIn('id', $request->arsip_ids)->get();
        foreach ($arsipTerpilih as $a) {
            if (!$a->aktif || $a->status_ketersediaan === 'Hilang') {
                return back()->withErrors(['arsip_ids' => "Arsip {$a->kode_internal} tidak aktif atau berstatus Hilang."])->withInput();
            }
            if ($bentukLayanan === 'Fisik' && $a->status_ketersediaan !== 'Tersedia') {
                return back()->withErrors(['arsip_ids' => "Arsip {$a->kode_internal} saat ini berstatus {$a->status_ketersediaan} dan tidak dapat dipinjam fisik."])->withInput();
            }
        }

        // 2. Data Peminjam
        if ($request->tipe_peminjam === 'terdaftar') {
            $request->validate(['peminjam_id' => 'required|exists:peminjam,id']);
            $peminjam = Peminjam::findOrFail($request->peminjam_id);
        } else {
            $request->validate([
                'nama_peminjam' => 'required|string|max:150',
                'jenis_peminjam' => ['required', Rule::in(array_keys(Peminjam::JENIS_LABELS))],
                'instansi_unit' => 'required|string|max:150',
                'no_hp' => 'required|string|max:25',
                'nip_nik' => 'nullable|string|max:30',
                'alamat' => $request->jenis_peminjam === 'Eksternal' ? 'required|string|max:255' : 'nullable|string|max:255',
            ]);

            $peminjam = Peminjam::create([
                'nama' => $request->nama_peminjam,
                'jenis' => $request->jenis_peminjam,
                'instansi_unit' => $request->instansi_unit,
                'no_hp' => $request->no_hp,
                'nip_nik' => $request->nip_nik,
                'jabatan' => $request->jabatan_peminjam,
                'alamat' => $request->alamat,
                'jaminan' => $jaminanInput,
            ]);
        }

        $jaminanFinal = $jaminanInput ?? $peminjam->jaminan;

        DB::beginTransaction();
        try {
            // 3. Generate Nomor Surat Otomatis: NNN/SPA/KODE-BIDANG/BULAN-ROMAWI/TAHUN
            $tahunSekarang = (int) date('Y');
            $counter = DB::table('nomor_surat_counter')
                ->where('unit_pengolah_id', $unit->id)
                ->where('tahun', $tahunSekarang)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                DB::table('nomor_surat_counter')->insert([
                    'unit_pengolah_id' => $unit->id,
                    'tahun' => $tahunSekarang,
                    'terakhir' => 1,
                ]);
                $nomorUrut = 1;
            } else {
                $nomorUrut = $counter->terakhir + 1;
                DB::table('nomor_surat_counter')
                    ->where('unit_pengolah_id', $unit->id)
                    ->where('tahun', $tahunSekarang)
                    ->update(['terakhir' => $nomorUrut]);
            }

            $urutStr = str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
            $bulanRomawi = $this->getBulanRomawi((int) date('n'));
            $nomorSurat = "{$urutStr}/SPA/{$unit->kode}/{$bulanRomawi}/{$tahunSekarang}";

            // 4. Simpan Tanda Tangan
            $metodeTtd = $request->metode_ttd ?: 'Manual';
            $ttdPath = null;
            $suratTtdPath = null;

            if ($request->filled('ttd_digital_data') && str_contains($request->ttd_digital_data, 'base64,')) {
                $dataUri = $request->ttd_digital_data;
                $encodedData = substr($dataUri, strpos($dataUri, ',') + 1);
                $decoded = base64_decode($encodedData);
                if ($decoded !== false && strlen($decoded) > 100) {
                    $filename = 'ttd_' . Str::uuid() . '.png';
                    Storage::disk('public')->put("ttd/{$filename}", $decoded);
                    $ttdPath = "ttd/{$filename}";
                    $metodeTtd = 'Digital';
                }
            } elseif ($request->hasFile('surat_ttd_file')) {
                $suratTtdPath = $request->file('surat_ttd_file')->store('surat_manual', 'public');
                $metodeTtd = 'Manual';
            } else {
                $metodeTtd = 'Manual';
            }

            // 5. Buat Surat Peminjaman
            $surat = SuratPeminjaman::create([
                'nomor_surat' => $nomorSurat,
                'tanggal_surat' => now()->toDateString(),
                'unit_pengolah_id' => $unit->id,
                'peminjam_id' => $peminjam->id,
                'bentuk_layanan' => $bentukLayanan,
                'is_janjian' => false,
                'rencana_ambil_pada' => null,
                'catatan_janjian' => null,
                'maksud_keperluan' => $request->maksud_keperluan,
                'jaminan' => $jaminanFinal,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_kembali' => $request->tanggal_kembali,
                'lama_pinjam_hari' => $durasiHari,
                'butuh_persetujuan' => true,
                'status_persetujuan' => 'Menunggu',
                'metode_ttd' => $metodeTtd,
                'ttd_peminjam_path' => $ttdPath,
                'surat_ttd_path' => $suratTtdPath,
                'petugas_id' => $user->id,
                'petugas_layanan_nama' => $petugasLayanan['nama'],
                'petugas_layanan_nip' => $petugasLayanan['nip'],
                'petugas_layanan_bidang' => $petugasLayanan['bidang'],
                'status' => 'Menunggu Persetujuan',
            ]);

            // 6. Simpan Item Berkas
            foreach ($request->arsip_ids as $arsipId) {
                SuratPeminjamanItem::create([
                    'surat_id' => $surat->id,
                    'arsip_id' => $arsipId,
                    'kondisi_keluar' => 'Baik',
                ]);
            }

            // 7. Simpan Foto Bukti Serah Terima jika ada
            if ($request->filled('foto_bukti_data')) {
                $dataUri = $request->foto_bukti_data;
                $encodedData = substr($dataUri, strpos($dataUri, ',') + 1);
                $decoded = base64_decode($encodedData);
                $filename = 'bukti_' . Str::uuid() . '.jpg';
                Storage::disk('public')->put("bukti/{$filename}", $decoded);

                $jenisFoto = in_array($request->input('foto_bukti_jenis'), ['Serah Terima', 'Identitas', 'Surat Bertanda Tangan', 'Pengembalian'])
                    ? $request->input('foto_bukti_jenis')
                    : 'Serah Terima';

                BuktiFoto::create([
                    'surat_id' => $surat->id,
                    'jenis' => $jenisFoto,
                    'sumber' => $request->input('foto_bukti_sumber', 'Webcam'),
                    'file_path' => "bukti/{$filename}",
                    'diunggah_oleh' => $user->id,
                ]);
            }

            // 8. Log Audit
            LogAudit::create([
                'user_id' => $user->id,
                'aksi' => 'Buat Surat Peminjaman',
                'entitas' => 'SuratPeminjaman',
                'entitas_id' => $surat->id,
                'detail' => [
                    'nomor_surat' => $nomorSurat,
                    'peminjam' => $peminjam->nama,
                    'bentuk_layanan' => $bentukLayanan,
                    'petugas_layanan' => $petugasLayanan['nama'],
                    'petugas_layanan_bidang' => $petugasLayanan['bidang'],
                    'jumlah_arsip' => count($request->arsip_ids),
                ],
            ]);

            DB::commit();

            return redirect()->route('peminjaman.show', $surat)->with('sukses', "Surat Peminjaman {$nomorSurat} berhasil dibuat dan diajukan ke Super Admin / Kepala Bidang (Kabid).");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat surat: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SuratPeminjaman $peminjaman)
    {
        $user = Auth::user();
        if (!$peminjaman->dapatDiaksesOleh($user)) {
            abort(403, 'Anda tidak memiliki akses ke surat peminjaman ini.');
        }

        $peminjaman->load(['peminjam', 'unitPengolah', 'petugas.unitPengolah', 'disetujuiOleh', 'items.arsip.unitPengolah', 'buktiFoto', 'perpanjangan.disetujuiOleh']);
        return view('peminjaman.show', compact('peminjaman'));
    }

    // Persetujuan oleh Super Admin / Kepala Bidang (FR-17, FR-39)
    public function setujui(Request $request, SuratPeminjaman $peminjaman)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin / Kepala Bidang (Kabid) yang dapat menyetujui peminjaman.');
        }

        if ($peminjaman->status !== 'Menunggu Persetujuan') {
            return back()->with('error', 'Surat peminjaman ini sudah diproses dan tidak berada pada status Menunggu Persetujuan.');
        }

        $peminjaman->load('items.arsip');

        DB::beginTransaction();
        try {
            $statusBaru = $peminjaman->is_janjian ? 'Janjian' : 'Aktif';

            // Hanya peminjaman Fisik non-janjian yang langsung mengunci ketersediaan fisik
            if ($statusBaru === 'Aktif' && $peminjaman->isFisik()) {
                foreach ($peminjaman->items as $item) {
                    if ($item->arsip->status_ketersediaan !== 'Tersedia') {
                        DB::rollBack();
                        return back()->with('error', "Arsip {$item->arsip->kode_internal} saat ini berstatus {$item->arsip->status_ketersediaan} sehingga tidak dapat diserahkan secara fisik.");
                    }
                }
                foreach ($peminjaman->items as $item) {
                    $item->arsip->update(['status_ketersediaan' => 'Dipinjam']);
                }
            }

            $peminjaman->update([
                'status_persetujuan' => 'Disetujui',
                'disetujui_oleh' => $user->id,
                'tanggal_disetujui' => now(),
                'status' => $statusBaru,
            ]);

            LogAudit::create([
                'user_id' => $user->id,
                'aksi' => 'Setujui Peminjaman',
                'entitas' => 'SuratPeminjaman',
                'entitas_id' => $peminjaman->id,
                'detail' => [
                    'nomor_surat' => $peminjaman->nomor_surat,
                    'bentuk_layanan' => $peminjaman->bentuk_layanan,
                    'is_janjian' => $peminjaman->is_janjian,
                    'status_baru' => $statusBaru,
                ],
            ]);

            DB::commit();

            if ($statusBaru === 'Janjian') {
                return back()->with('sukses', "Surat Peminjaman {$peminjaman->nomor_surat} disetujui (Jadwal Janjian). Berkas belum dikunci sampai peminjam hadir dan dilakukan Serah Terima.");
            }
            if ($peminjaman->isFisik()) {
                return back()->with('sukses', "Surat Peminjaman {$peminjaman->nomor_surat} telah disetujui. Berkas fisik resmi berstatus Dipinjam.");
            }
            return back()->with('sukses', "Permohonan layanan {$peminjaman->bentuk_layanan} ({$peminjaman->nomor_surat}) telah disetujui. Ketersediaan berkas fisik tetap Tersedia.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui: ' . $e->getMessage());
        }
    }

    // Konfirmasi Serah Terima atas Janjian / Peminjaman Dipesan
    public function serahTerimakan(Request $request, SuratPeminjaman $peminjaman)
    {
        $user = Auth::user();
        if (!$peminjaman->dapatDiaksesOleh($user)) {
            abort(403, 'Anda tidak memiliki akses untuk memproses serah terima surat ini.');
        }

        if ($peminjaman->status !== 'Janjian') {
            return back()->with('error', 'Serah terima hanya dapat dilakukan pada peminjaman berstatus Janjian.');
        }

        $peminjaman->load('items.arsip');

        DB::beginTransaction();
        try {
            if ($peminjaman->isFisik()) {
                foreach ($peminjaman->items as $item) {
                    if ($item->arsip->status_ketersediaan !== 'Tersedia') {
                        DB::rollBack();
                        return back()->with('error', "Arsip {$item->arsip->kode_internal} sedang berstatus {$item->arsip->status_ketersediaan} dan belum tersedia untuk serah terima fisik.");
                    }
                }
                foreach ($peminjaman->items as $item) {
                    $item->arsip->update(['status_ketersediaan' => 'Dipinjam']);
                }
            }

            $peminjaman->update([
                'status' => 'Aktif',
            ]);

            LogAudit::create([
                'user_id' => $user->id,
                'aksi' => 'Serah Terima Janjian',
                'entitas' => 'SuratPeminjaman',
                'entitas_id' => $peminjaman->id,
                'detail' => [
                    'nomor_surat' => $peminjaman->nomor_surat,
                    'bentuk_layanan' => $peminjaman->bentuk_layanan,
                ],
            ]);

            DB::commit();

            $msg = $peminjaman->isFisik()
                ? "Serah terima fisik {$peminjaman->nomor_surat} berhasil dikonfirmasi. Status peminjaman kini Aktif dan berkas dikunci Dipinjam."
                : "Serah terima layanan {$peminjaman->bentuk_layanan} ({$peminjaman->nomor_surat}) berhasil dikonfirmasi (Aktif).";

            return back()->with('sukses', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses serah terima: ' . $e->getMessage());
        }
    }

    // Batalkan Janjian / Permohonan Sebelum Berkas Diserahkan
    public function batalkanJanjian(Request $request, SuratPeminjaman $peminjaman)
    {
        $user = Auth::user();
        if (!$peminjaman->dapatDiaksesOleh($user)) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan surat ini.');
        }

        if (!in_array($peminjaman->status, ['Janjian', 'Menunggu Persetujuan'])) {
            return back()->with('error', 'Hanya permohonan Janjian atau Menunggu Persetujuan yang dapat dibatalkan.');
        }

        $peminjaman->update([
            'status' => 'Dibatalkan',
            'alasan_penolakan' => $request->input('alasan_batal') ?: ($peminjaman->alasan_penolakan ?: 'Dibatalkan sebelum serah terima berkas.'),
        ]);

        LogAudit::create([
            'user_id' => $user->id,
            'aksi' => 'Batalkan Janjian',
            'entitas' => 'SuratPeminjaman',
            'entitas_id' => $peminjaman->id,
            'detail' => ['nomor_surat' => $peminjaman->nomor_surat],
        ]);

        return back()->with('sukses', "Jadwal/permohonan {$peminjaman->nomor_surat} telah dibatalkan.");
    }

    public function tolak(Request $request, SuratPeminjaman $peminjaman)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin / Kepala Bidang (Kabid) yang dapat menolak peminjaman.');
        }

        if ($peminjaman->status !== 'Menunggu Persetujuan') {
            return back()->with('error', 'Surat peminjaman ini tidak berada pada status Menunggu Persetujuan.');
        }

        $request->validate(['alasan_penolakan' => 'required|string|min:5']);

        $peminjaman->update([
            'status_persetujuan' => 'Ditolak',
            'disetujui_oleh' => $user->id,
            'tanggal_disetujui' => now(),
            'status' => 'Ditolak',
            'alasan_penolakan' => $request->alasan_penolakan,
        ]);

        return back()->with('sukses', "Surat Peminjaman {$peminjaman->nomor_surat} telah ditolak.");
    }

    // Perpanjangan Masa Peminjaman (FR-20)
    public function perpanjang(Request $request, SuratPeminjaman $peminjaman)
    {
        $user = Auth::user();
        if (!$peminjaman->dapatDiaksesOleh($user)) {
            abort(403);
        }

        if (!in_array($peminjaman->status, ['Aktif', 'Terlambat'])) {
            return back()->with('error', 'Hanya peminjaman aktif atau terlambat yang dapat diperpanjang.');
        }

        if ($peminjaman->diperpanjang_kali >= 2) {
            return back()->with('error', 'Peminjaman ini sudah mencapai batas maksimal perpanjangan (2 kali).');
        }

        $request->validate([
            'tanggal_kembali_baru' => 'required|date|after:' . $peminjaman->tanggal_kembali->format('Y-m-d'),
            'alasan' => 'required|string|min:5|max:255',
        ]);

        $tglLama = $peminjaman->tanggal_kembali;
        $tglBaru = Carbon::parse($request->tanggal_kembali_baru);
        $selisihHari = $tglLama->diffInDays($tglBaru);

        $unit = $peminjaman->unitPengolah;
        $maxHariPerpanjangan = $unit->lama_pinjam_maks ?: 7;

        if ($selisihHari > $maxHariPerpanjangan) {
            return back()->with('error', "Perpanjangan tidak boleh melebihi {$maxHariPerpanjangan} hari dari tanggal kembali sebelumnya.");
        }

        DB::beginTransaction();
        try {
            Perpanjangan::create([
                'surat_id' => $peminjaman->id,
                'tanggal_kembali_lama' => $tglLama,
                'tanggal_kembali_baru' => $tglBaru,
                'alasan' => $request->alasan,
                'disetujui_oleh' => $user->id,
            ]);

            $peminjaman->update([
                'tanggal_kembali' => $tglBaru,
                'lama_pinjam_hari' => $peminjaman->lama_pinjam_hari + $selisihHari,
                'diperpanjang_kali' => $peminjaman->diperpanjang_kali + 1,
                'status' => 'Aktif', // pulihkan jika sebelumnya Terlambat
            ]);

            LogAudit::create([
                'user_id' => $user->id,
                'aksi' => 'Perpanjang Peminjaman',
                'entitas' => 'SuratPeminjaman',
                'entitas_id' => $peminjaman->id,
                'detail' => [
                    'nomor_surat' => $peminjaman->nomor_surat,
                    'tanggal_lama' => $tglLama->toDateString(),
                    'tanggal_baru' => $tglBaru->toDateString(),
                    'alasan' => $request->alasan,
                ],
            ]);

            DB::commit();
            return back()->with('sukses', "Masa peminjaman berhasil diperpanjang hingga {$tglBaru->format('d/m/Y')}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperpanjang peminjaman: ' . $e->getMessage());
        }
    }

    // Cetak Surat Peminjaman Resmi PDF (FR-12, FR-25)
    public function cetakPdf(SuratPeminjaman $peminjaman)
    {
        $user = Auth::user();
        if (!$peminjaman->dapatDiaksesOleh($user)) {
            abort(403);
        }

        $peminjaman->load(['peminjam', 'unitPengolah', 'petugas.unitPengolah', 'disetujuiOleh', 'items.arsip.unitPengolah']);
        $pengaturan = Pengaturan::pluck('nilai', 'kunci');

        $pdf = Pdf::loadView('peminjaman.pdf', compact('peminjaman', 'pengaturan'))
            ->setPaper('a4', 'portrait');

        $cleanNomor = str_replace('/', '_', $peminjaman->nomor_surat);
        return $pdf->download("Surat_Peminjaman_{$cleanNomor}.pdf");
    }

    private function resolvePetugasLayanan(Request $request, User $user, UnitPengolah $unit): array
    {
        $namaInput = trim((string) $request->input('petugas_layanan_nama', ''));
        $nipInput = trim((string) $request->input('petugas_layanan_nip', ''));
        $bidangInput = trim((string) $request->input('petugas_layanan_bidang', ''));

        $isGenericSuperAdmin = strcasecmp(trim((string) $user->name), 'Super Admin SIPA') === 0;

        if ($isGenericSuperAdmin && ($namaInput === '' || strcasecmp($namaInput, 'Super Admin SIPA') === 0)) {
            return ['error' => 'Harap isi nama petugas yang melayani (bukan Super Admin SIPA).'];
        }

        if ($namaInput !== '' && strcasecmp($namaInput, 'Super Admin SIPA') === 0) {
            return ['error' => 'Nama petugas layanan tidak boleh menggunakan "Super Admin SIPA".'];
        }

        return [
            'nama' => $namaInput !== '' ? $namaInput : $user->name,
            'nip' => $nipInput !== '' ? $nipInput : ($namaInput === '' ? $user->nip : null),
            'bidang' => $bidangInput !== '' ? $bidangInput : ($user->unitPengolah?->nama ?: $unit->nama),
        ];
    }

    private function getBulanRomawi(int $bulan): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $romawi[$bulan] ?? 'I';
    }
}
