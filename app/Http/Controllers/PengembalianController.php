<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\BuktiFoto;
use App\Models\LogAudit;
use App\Models\SuratPeminjaman;
use App\Models\SuratPeminjamanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = SuratPeminjaman::with(['peminjam', 'unitPengolah', 'items.arsip'])
            ->whereIn('status', ['Aktif', 'Terlambat']);

        if ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
            $query->where(function ($sub) use ($user) {
                $sub->where('unit_pengolah_id', $user->unit_pengolah_id)
                    ->orWhere('petugas_id', $user->id);
            });
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $like = $this->likeOperator();
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('nomor_surat', $like, "%{$q}%")
                    ->orWhereHas('peminjam', function ($pq) use ($q, $like) {
                        $pq->where('nama', $like, "%{$q}%");
                    })
                    ->orWhereHas('items.arsip', function ($aq) use ($q, $like) {
                        $aq->where('kode_internal', $like, "%{$q}%")
                           ->orWhere('uraian_berkas', $like, "%{$q}%");
                    });
            });
        }

        $peminjamanAktif = $query->latest('id')->paginate(10)->withQueryString();
        return view('pengembalian.index', compact('peminjamanAktif'));
    }

    public function proses(Request $request, SuratPeminjaman $peminjaman)
    {
        $user = Auth::user();
        if (!$peminjaman->dapatDiaksesOleh($user)) {
            abort(403, 'Anda tidak berwenang memproses pengembalian unit pengolah lain.');
        }

        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:surat_peminjaman_item,id',
            'kondisi_kembali' => 'required|array',
            'skor_integritas' => 'nullable|array',
            'skor_integritas.*' => 'nullable|integer|min:0|max:100',
            'ringkasan_kerusakan' => 'nullable|array',
            'detail_kerusakan_json' => 'nullable|array',
            'foto_kembali_data' => 'nullable|array',
        ]);

        $laporanCnn = [];

        DB::beginTransaction();
        try {
            foreach ($request->item_ids as $itemId) {
                $item = SuratPeminjamanItem::where('surat_id', $peminjaman->id)->findOrFail($itemId);
                $kondisi = $request->kondisi_kembali[$itemId] ?? 'Baik';
                $catatan = $request->catatan[$itemId] ?? null;
                $skorRaw = $request->skor_integritas[$itemId] ?? null;
                $skorIntegritas = ($skorRaw !== null && $skorRaw !== '') ? (int) $skorRaw : null;
                $ringkasan = $request->ringkasan_kerusakan[$itemId] ?? null;

                $detailJson = null;
                $detailRaw = $request->detail_kerusakan_json[$itemId] ?? null;
                if (is_string($detailRaw) && $detailRaw !== '') {
                    $decodedJson = json_decode($detailRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $detailJson = $decodedJson;
                    }
                } elseif (is_array($detailRaw)) {
                    $detailJson = $detailRaw;
                }

                $fotoKembaliPath = $this->saveBase64Image(
                    $request->foto_kembali_data[$itemId] ?? null,
                    'bukti',
                    'kembali'
                );

                if ($fotoKembaliPath) {
                    BuktiFoto::create([
                        'surat_id' => $peminjaman->id,
                        'jenis' => 'Pengembalian',
                        'sumber' => 'Kamera CNN',
                        'file_path' => $fotoKembaliPath,
                        'diunggah_oleh' => $user->id,
                    ]);
                }

                $item->update([
                    'tanggal_kembali_riil' => now(),
                    'kondisi_kembali' => $kondisi,
                    'skor_integritas' => $skorIntegritas,
                    'ringkasan_kerusakan' => $ringkasan ? mb_substr($ringkasan, 0, 255) : null,
                    'detail_kerusakan_json' => $detailJson,
                    'foto_kembali_path' => $fotoKembaliPath,
                    'catatan' => $catatan ?: $ringkasan,
                ]);

                // Pulihkan status ketersediaan & riwayat kondisi fisik hanya untuk layanan peminjaman Fisik
                if ($peminjaman->isFisik()) {
                    $statusArsip = $kondisi === 'Baik' ? 'Tersedia' : ($kondisi === 'Rusak' ? 'Rusak' : 'Hilang');
                    $arsipUpdate = ['status_ketersediaan' => $statusArsip];
                    if ($skorIntegritas !== null || $ringkasan) {
                        $arsipUpdate['skor_integritas_terakhir'] = $skorIntegritas;
                        $arsipUpdate['catatan_kondisi_terakhir'] = $ringkasan ? mb_substr($ringkasan, 0, 255) : null;
                        $arsipUpdate['terakhir_diperiksa_pada'] = now();
                        $laporanCnn[] = ['arsip_id' => $item->arsip_id, 'skor' => $skorIntegritas, 'ringkasan' => $ringkasan];
                    }
                    $item->arsip->update($arsipUpdate);
                }
            }

            // Periksa apakah semua berkas di surat ini sudah kembali
            $sisaBelumKembali = SuratPeminjamanItem::where('surat_id', $peminjaman->id)
                ->whereNull('tanggal_kembali_riil')
                ->count();

            if ($sisaBelumKembali === 0) {
                $peminjaman->update([
                    'status' => 'Selesai',
                    'tanggal_selesai' => now(),
                ]);
            }

            LogAudit::create([
                'user_id' => $user->id,
                'aksi' => $peminjaman->isFisik() ? 'Pengembalian Arsip' : 'Selesai Layanan Non-Fisik',
                'entitas' => 'SuratPeminjaman',
                'entitas_id' => $peminjaman->id,
                'detail' => [
                    'nomor_surat' => $peminjaman->nomor_surat,
                    'bentuk_layanan' => $peminjaman->bentuk_layanan,
                    'item_dikembalikan' => count($request->item_ids),
                    'surat_selesai' => ($sisaBelumKembali === 0),
                    'penilaian_cnn' => $laporanCnn,
                ],
            ]);

            DB::commit();

            $pesanSukses = $peminjaman->isFisik()
                ? "Pengembalian berkas fisik untuk surat {$peminjaman->nomor_surat} berhasil dicatat."
                : "Layanan {$peminjaman->bentuk_layanan} untuk surat {$peminjaman->nomor_surat} telah dikonfirmasi selesai.";

            return redirect()->route('pengembalian.index')->with('sukses', $pesanSukses);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}
