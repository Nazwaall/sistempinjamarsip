<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\SuratPeminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DasborController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $unitId = $user->unit_pengolah_id;

        // Query arsip
        $arsipQuery = Arsip::where('aktif', true);
        if ($unitId && !$user->isSuperAdmin()) {
            $arsipQuery->where('unit_pengolah_id', $unitId);
        }
        $totalArsip = (clone $arsipQuery)->count();
        $arsipTersedia = (clone $arsipQuery)->where('status_ketersediaan', 'Tersedia')->count();

        // Query peminjaman
        $suratQuery = SuratPeminjaman::query();
        if ($unitId && !$user->isSuperAdmin()) {
            $suratQuery->where(function ($sub) use ($unitId, $user) {
                $sub->where('unit_pengolah_id', $unitId)->orWhere('petugas_id', $user->id);
            });
        }

        $hariIni = now()->toDateString();

        $totalPinjamAktif = (clone $suratQuery)->whereIn('status', ['Aktif', 'Terlambat'])
            ->whereDate('tanggal_kembali', '>=', $hariIni)
            ->count();
        $totalMenunggu = (clone $suratQuery)->where('status', 'Menunggu Persetujuan')->count();
        $totalJanjian = (clone $suratQuery)->where('status', 'Janjian')->count();
        $totalTerlambat = (clone $suratQuery)->whereIn('status', ['Aktif', 'Terlambat'])
            ->whereDate('tanggal_kembali', '<', $hariIni)
            ->count();
        $totalSelesai = (clone $suratQuery)->where('status', 'Selesai')->count();

        // Peminjaman terbaru
        $peminjamanTerbaru = (clone $suratQuery)
            ->with(['peminjam', 'unitPengolah', 'items.arsip'])
            ->latest('id')
            ->take(5)
            ->get();

        // Peringatan jatuh tempo (maks H-2) & terlambat
        $perluPerhatian = (clone $suratQuery)
            ->with(['peminjam', 'items.arsip'])
            ->whereIn('status', ['Aktif', 'Terlambat'])
            ->whereDate('tanggal_kembali', '<=', now()->addDays(2)->toDateString())
            ->orderBy('tanggal_kembali', 'asc')
            ->take(5)
            ->get();

        return view('dasbor.index', compact(
            'user',
            'totalArsip',
            'arsipTersedia',
            'totalPinjamAktif',
            'totalMenunggu',
            'totalJanjian',
            'totalTerlambat',
            'totalSelesai',
            'peminjamanTerbaru',
            'perluPerhatian'
        ));
    }

    public function panduan()
    {
        $user = Auth::user();
        return view('panduan.index', compact('user'));
    }
}
