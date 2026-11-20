<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Pengaturan;
use App\Models\SuratPeminjaman;
use App\Models\SuratPeminjamanItem;
use App\Models\UnitPengolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = $request->filled('bulan') && $request->bulan !== '' ? (int) $request->bulan : null;

        $query = $this->buildScopedQuery($request, $user, $tahun, $bulan);
        $metrics = $this->buildReportMetrics($query, $request, $user, $tahun, $bulan);

        $daftarSurat = (clone $query)->latest('id')->paginate(15)->withQueryString();
        $unitList = UnitPengolah::where('aktif', true)->orderBy('id')->get();
        $tahunList = SuratPeminjaman::selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as th')
            ->distinct()
            ->orderByDesc('th')
            ->pluck('th');

        if ($tahunList->isEmpty()) {
            $tahunList = collect([date('Y')]);
        }

        $selectedUnitObj = null;
        if ($request->filled('unit_pengolah_id')) {
            $selectedUnitObj = UnitPengolah::find($request->unit_pengolah_id);
        } elseif ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
            $selectedUnitObj = $user->unitPengolah;
        }

        return view('laporan.index', array_merge($metrics, [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'daftarSurat' => $daftarSurat,
            'unitList' => $unitList,
            'tahunList' => $tahunList,
            'selectedUnitObj' => $selectedUnitObj,
        ]));
    }

    // Ekspor Excel (.xlsx via PhpSpreadsheet)
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = $request->filled('bulan') && $request->bulan !== '' ? (int) $request->bulan : null;

        $unitDipilih = null;
        if ($request->filled('unit_pengolah_id')) {
            $unitDipilih = UnitPengolah::find($request->unit_pengolah_id);
        } elseif ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
            $unitDipilih = $user->unitPengolah;
        }

        $data = $this->buildScopedQuery($request, $user, $tahun, $bulan)->latest('id')->get();
        $spreadsheet = $this->buildRekapSpreadsheet($data, $tahun, $bulan, $unitDipilih);

        $writer = new Xlsx($spreadsheet);
        $namaBulanFile = $bulan ? "_Bulan_{$bulan}" : "";
        $unitFile = $unitDipilih ? "_{$unitDipilih->kode}" : "";
        $fileName = "Rekap_Peminjaman_dan_Pengembalian_Arsip_{$tahun}{$namaBulanFile}{$unitFile}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    // Ekspor PDF (.pdf via DomPDF)
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = $request->filled('bulan') && $request->bulan !== '' ? (int) $request->bulan : null;

        $query = $this->buildScopedQuery($request, $user, $tahun, $bulan);
        $metrics = $this->buildReportMetrics($query, $request, $user, $tahun, $bulan);

        $daftarSurat = (clone $query)->latest('id')->get();
        $pengaturan = Pengaturan::pluck('nilai', 'kunci');

        $unitDipilih = null;
        if ($request->filled('unit_pengolah_id')) {
            $unitDipilih = UnitPengolah::find($request->unit_pengolah_id);
        } elseif ($user->unit_pengolah_id && !$user->isSuperAdmin()) {
            $unitDipilih = $user->unitPengolah;
        }

        $pdf = Pdf::loadView('laporan.pdf', array_merge($metrics, [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'daftarSurat' => $daftarSurat,
            'pengaturan' => $pengaturan,
            'unitDipilih' => $unitDipilih,
            'dicetakOleh' => $user->name,
        ]))->setPaper('a4', 'landscape');

        $namaBulanFile = $bulan ? "_Bulan_{$bulan}" : "";
        $unitFile = $unitDipilih ? "_{$unitDipilih->kode}" : "";
        $fileName = "Laporan_Rekapitulasi_Peminjaman_dan_Pengembalian_Arsip_Tahun_{$tahun}{$namaBulanFile}{$unitFile}.pdf";

        return $pdf->download($fileName);
    }

    protected function buildScopedQuery(Request $request, $user, int $tahun, ?int $bulan = null)
    {
        $unitId = $user->unit_pengolah_id;

        $query = SuratPeminjaman::with(['peminjam', 'unitPengolah', 'items.arsip'])
            ->whereYear('tanggal_mulai', $tahun);

        if ($bulan) {
            $query->whereMonth('tanggal_mulai', $bulan);
        }

        if ($unitId && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($unitId, $user) {
                $q->where('unit_pengolah_id', $unitId)
                  ->orWhere('petugas_id', $user->id);
            });
        }

        if ($request->filled('unit_pengolah_id')) {
            $query->where('unit_pengolah_id', $request->unit_pengolah_id);
        }

        return $query;
    }

    protected function buildReportMetrics($query, Request $request, $user, int $tahun, ?int $bulan = null): array
    {
        $unitId = $user->unit_pengolah_id;

        $totalPeminjaman = (clone $query)->count();
        $totalMenunggu = (clone $query)->where('status', 'Menunggu Persetujuan')->count();
        $totalJanjian = (clone $query)->where('status', 'Janjian')->count();
        $totalDikembalikan = (clone $query)->where('status', 'Selesai')->count();
        $totalAktif = (clone $query)->where('status', 'Aktif')->where('tanggal_kembali', '>=', now()->toDateString())->count();
        $totalTerlambat = (clone $query)->where(function ($q) {
            $q->where('status', 'Terlambat')
              ->orWhere(function ($sub) {
                  $sub->where('status', 'Aktif')
                      ->where('tanggal_kembali', '<', now()->toDateString());
              });
        })->count();
        $totalFisik = (clone $query)->where('bentuk_layanan', 'Fisik')->count();
        $totalDigital = (clone $query)->where('bentuk_layanan', 'Digital (PDF)')->count();
        $totalInformasi = (clone $query)->whereIn('bentuk_layanan', ['Layanan Informasi', 'Layanan Informasi (Statis)'])->count();

        $peminjamanInternal = (clone $query)->whereHas('peminjam', fn($q) => $q->where('jenis', 'Internal'))->count();
        $peminjamanSkpd = (clone $query)->whereHas('peminjam', fn($q) => $q->where('jenis', 'SKPD'))->count();
        $peminjamanKecamatan = (clone $query)->whereHas('peminjam', fn($q) => $q->where('jenis', 'Kecamatan / Kelurahan'))->count();
        $peminjamanEksternal = (clone $query)->whereHas('peminjam', fn($q) => $q->where('jenis', 'Eksternal'))->count();

        // Rekap Bulanan 12 bulan sepanjang tahun
        $rekapBulananQuery = SuratPeminjaman::whereYear('tanggal_mulai', $tahun);
        if ($unitId && !$user->isSuperAdmin()) {
            $rekapBulananQuery->where(function ($q) use ($unitId, $user) {
                $q->where('unit_pengolah_id', $unitId)
                  ->orWhere('petugas_id', $user->id);
            });
        }
        if ($request->filled('unit_pengolah_id')) {
            $rekapBulananQuery->where('unit_pengolah_id', $request->unit_pengolah_id);
        }

        $rekapBulanan = $rekapBulananQuery
            ->selectRaw('EXTRACT(MONTH FROM tanggal_mulai) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // 10 Arsip Terbanyak Dipinjam pada periode yang difilter
        $arsipTerbanyak = SuratPeminjamanItem::select('arsip_id', DB::raw('COUNT(*) as total_pinjam'))
            ->whereHas('surat', function ($sq) use ($tahun, $bulan, $unitId, $user, $request) {
                $sq->whereYear('tanggal_mulai', $tahun);
                if ($bulan) {
                    $sq->whereMonth('tanggal_mulai', $bulan);
                }
                if ($unitId && !$user->isSuperAdmin()) {
                    $sq->where(function ($q) use ($unitId, $user) {
                        $q->where('unit_pengolah_id', $unitId)
                          ->orWhere('petugas_id', $user->id);
                    });
                }
                if ($request->filled('unit_pengolah_id')) {
                    $sq->where('unit_pengolah_id', $request->unit_pengolah_id);
                }
            })
            ->with('arsip')
            ->groupBy('arsip_id')
            ->orderByDesc('total_pinjam')
            ->limit(10)
            ->get();

        return compact(
            'totalPeminjaman',
            'totalMenunggu',
            'totalJanjian',
            'totalDikembalikan',
            'totalAktif',
            'totalTerlambat',
            'totalFisik',
            'totalDigital',
            'totalInformasi',
            'peminjamanInternal',
            'peminjamanSkpd',
            'peminjamanKecamatan',
            'peminjamanEksternal',
            'rekapBulanan',
            'arsipTerbanyak'
        );
    }

    /**
     * Membangun objek Spreadsheet rekapitulasi peminjaman & pengembalian tahunan / bulanan.
     */
    protected function buildRekapSpreadsheet($data, $tahun, ?int $bulan = null, ?UnitPengolah $unitDipilih = null): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $namaBulanLengkap = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];

        $periodeStr = $bulan ? 'BULAN ' . $namaBulanLengkap[$bulan] . ' TAHUN ' . $tahun : 'TAHUN ' . $tahun;
        $sheetTitle = $bulan ? substr($namaBulanLengkap[$bulan], 0, 3) . " {$tahun}" : "Rekap {$tahun}";
        $sheet->setTitle($sheetTitle);

        // Judul Header
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI PEMINJAMAN & PENGEMBALIAN ARSIP ' . $periodeStr);
        $sheet->setCellValue('A2', 'DINAS KEARSIPAN DAN PERPUSTAKAAN KABUPATEN CIREBON');

        $lingkupBidangStr = 'Lingkup Bidang: ' . ($unitDipilih ? $unitDipilih->nama . ' (' . $unitDipilih->kode . ')' : 'Seluruh Bidang (Tingkat Dinas)');
        $sheet->setCellValue('A3', $lingkupBidangStr . ' | Diekspor pada: ' . now()->translatedFormat('d F Y, H:i') . ' WIB');

        $sheet->mergeCells('A1:P1');
        $sheet->mergeCells('A2:P2');
        $sheet->mergeCells('A3:P3');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(13);
        $sheet->getStyle('A2')->getFont()->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A1:P3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Tabel
        $headers = [
            'No.',
            'Nomor Surat',
            'Bidang Pembuat Surat',
            'Bentuk Layanan',
            'Tanggal Pinjam',
            'Jatuh Tempo',
            'Tanggal Pengembalian',
            'Nama Peminjam',
            'Kategori Peminjam',
            'Instansi / Unit',
            'Nomor Telepon',
            'Jaminan',
            'Maksud / Keperluan',
            'Jumlah Berkas',
            'Kondisi Saat Kembali',
            'Status',
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $cell = $col . '5';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $col++;
        }
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Isi Data
        $rowNum = 6;
        foreach ($data as $idx => $item) {
            $isTerlambat = $item->isTerlambat();

            $tglPengembalian = '-';
            $statusStr = $item->status;

            if ($item->status === 'Menunggu Persetujuan') {
                $tglPengembalian = 'Belum Disetujui';
                $statusStr = 'Menunggu Persetujuan';
            } elseif ($item->status === 'Janjian') {
                $tglPengembalian = $item->rencana_ambil_pada
                    ? 'Janjian: ' . $item->rencana_ambil_pada->format('d/m/Y H:i')
                    : 'Jadwal Janjian';
                $statusStr = 'Janjian';
            } elseif ($item->status === 'Ditolak') {
                $tglPengembalian = 'Ditolak';
                $statusStr = 'Ditolak';
            } elseif ($item->status === 'Dibatalkan') {
                $tglPengembalian = 'Dibatalkan';
                $statusStr = 'Dibatalkan';
            } elseif ($item->status === 'Selesai') {
                if ($item->tanggal_selesai) {
                    $tglPengembalian = $item->tanggal_selesai->format('d/m/Y');
                } else {
                    $riil = $item->items->pluck('tanggal_kembali_riil')->filter()->sortDesc()->first();
                    $tglPengembalian = $riil ? \Carbon\Carbon::parse($riil)->format('d/m/Y') : ($item->updated_at ? $item->updated_at->format('d/m/Y') : 'Selesai');
                }
                $statusStr = 'Selesai';
            } elseif ($isTerlambat) {
                $tglPengembalian = 'Belum Kembali';
                $statusStr = 'Terlambat';
            } elseif ($item->status === 'Aktif') {
                $tglPengembalian = $item->isFisik() ? 'Sedang Dipinjam' : 'Layanan Berjalan';
                $statusStr = $item->isFisik() ? 'Dipinjam' : 'Aktif';
            }

            $kondisiKembali = '-';
            if ($item->status === 'Selesai') {
                $kondisiList = $item->items->pluck('kondisi_kembali')->filter()->unique();
                $kondisiKembali = $kondisiList->isNotEmpty()
                    ? $kondisiList->implode(', ')
                    : ($item->isFisik() ? 'Baik' : 'Non-Fisik');
            }

            $sheet->setCellValue('A' . $rowNum, $idx + 1);
            $sheet->setCellValue('B' . $rowNum, $item->nomor_surat);
            $sheet->setCellValue('C' . $rowNum, $item->unitPengolah?->nama ?? '-');
            $sheet->setCellValue('D' . $rowNum, $item->bentuk_layanan ?: 'Fisik');
            $sheet->setCellValue('E' . $rowNum, $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-');
            $sheet->setCellValue('F' . $rowNum, $item->tanggal_kembali ? $item->tanggal_kembali->format('d/m/Y') : '-');
            $sheet->setCellValue('G' . $rowNum, $tglPengembalian);
            $sheet->setCellValue('H' . $rowNum, $item->peminjam?->nama ?? '-');
            $sheet->setCellValue('I' . $rowNum, $item->peminjam?->jenisLabel() ?? ($item->peminjam?->jenis ?? '-'));
            $sheet->setCellValue('J' . $rowNum, $item->peminjam?->instansi_unit ?? '-');
            $sheet->setCellValueExplicit('K' . $rowNum, $item->peminjam?->no_hp ?: '-', DataType::TYPE_STRING);
            $sheet->setCellValue('L' . $rowNum, $item->jaminan ?: ($item->peminjam?->jaminan ?? '-'));
            $sheet->setCellValue('M' . $rowNum, $item->maksud_keperluan ?: '-');
            $sheet->setCellValue('N' . $rowNum, $item->items->count());
            $sheet->setCellValue('O' . $rowNum, $kondisiKembali);
            $sheet->setCellValue('P' . $rowNum, $statusStr);

            // Alignment & Styling
            foreach (['A', 'B', 'D', 'E', 'F', 'G', 'I', 'K', 'L', 'N', 'O', 'P'] as $centerCol) {
                $sheet->getStyle($centerCol . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('J' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('M' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

            $rowNum++;
        }

        // Auto size columns except Maksud / Keperluan
        $autoCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'N', 'O', 'P'];
        foreach ($autoCols as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $sheet->getColumnDimension('M')->setWidth(35);

        // Border Tabel jika ada data
        if ($data->isNotEmpty()) {
            $lastRow = $rowNum - 1;
            $tableRange = 'A5:P' . $lastRow;
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        }

        return $spreadsheet;
    }
}
