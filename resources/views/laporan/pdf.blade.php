<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>LAPORAN REKAPITULASI PEMINJAMAN &amp; PENGEMBALIAN ARSIP TAHUN {{ $tahun }}</title>
    <style>
        @page {
            margin: 1.1cm 1.4cm 1.2cm 1.4cm;
            size: A4 landscape;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        .kop-table td { border: none; padding: 0; vertical-align: middle; }
        .kop-logo { width: 78px; text-align: left; }
        .kop-logo img { height: 70px; width: auto; display: block; }
        .kop-teks { text-align: center; }
        .kop-teks .instansi-nama { margin: 0; font-size: 12.5pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; line-height: 1.2; }
        .kop-teks .dinas-nama { margin: 2px 0 3px 0; font-size: 15.5pt; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; line-height: 1.15; }
        .kop-teks .alamat-line { margin: 0; font-size: 9pt; line-height: 1.25; }
        .kop-teks .kontak-line { margin: 1px 0 0 0; font-size: 9pt; line-height: 1.25; }
        .kop-teks .kontak-line a { color: #0000ff; text-decoration: underline; }
        .kop-teks .kodepos-line { margin: 1px 0 0 0; font-size: 9pt; font-weight: bold; letter-spacing: 0.5px; }
        .kop-spacer { width: 78px; }
        .garis-kop { border-top: 2.5px solid #000; border-bottom: 1px solid #000; height: 2px; margin: 4px 0 12px 0; }

        .judul-laporan { text-align: center; margin-bottom: 10px; }
        .judul-laporan h2 { margin: 0; font-size: 11.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; }
        .judul-laporan .sub { margin-top: 2px; font-size: 8.5pt; color: #334155; }

        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 10px -6px; }
        .kpi-box {
            width: 25%;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 4px;
            padding: 7px 10px;
            vertical-align: top;
        }
        .kpi-label { font-size: 7.5pt; color: #475569; text-transform: uppercase; font-weight: bold; }
        .kpi-val { font-size: 15pt; font-weight: bold; color: #0f172a; margin: 2px 0; }
        .kpi-note { font-size: 7.5pt; color: #64748b; }

        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            border-left: 3px solid #1d4ed8;
            padding-left: 6px;
            margin: 10px 0 5px 0;
            text-transform: uppercase;
        }

        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.grid-table thead { display: table-header-group; }
        table.grid-table tr { page-break-inside: avoid; }
        table.grid-table th,
        table.grid-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 8pt;
        }
        table.grid-table th {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
        }
        .badge-ok { background: #dcfce7; color: #166534; border-color: #86efac; }
        .badge-warn { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .badge-err { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
        .badge-info { background: #e0f2fe; color: #075985; border-color: #7dd3fc; }

        .two-col { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .two-col td.col-half { width: 50%; vertical-align: top; padding: 0; }
        .two-col td.col-left { padding-right: 6px; }
        .two-col td.col-right { padding-left: 6px; }

        .ttd-footer {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .ttd-footer td { width: 50%; text-align: center; font-size: 8.5pt; vertical-align: top; }
        .ttd-gap { height: 48px; }
    </style>
</head>
<body>
    @include('partials.pdf-kop', ['pengaturan' => $pengaturan])

    @php
        $namaBulanLengkap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $judulPeriode = !empty($bulan) && isset($namaBulanLengkap[$bulan])
            ? 'BULAN ' . strtoupper($namaBulanLengkap[$bulan]) . ' TAHUN ' . $tahun
            : 'TAHUN ' . $tahun;
    @endphp

    <div class="judul-laporan">
        <h2>LAPORAN REKAPITULASI PEMINJAMAN &amp; PENGEMBALIAN ARSIP {{ $judulPeriode }}</h2>
        <div class="sub">
            Lingkup Bidang: <strong>{{ $unitDipilih ? $unitDipilih->nama . ' (' . $unitDipilih->kode . ')' : 'Seluruh Bidang (Tingkat Dinas)' }}</strong>
            &nbsp;|&nbsp; Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            &nbsp;|&nbsp; Oleh: {{ $dicetakOleh }}
        </div>
    </div>

    <!-- KPI Ringkasan -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <div class="kpi-label">Total Permohonan</div>
                <div class="kpi-val">{{ number_format($totalPeminjaman) }}</div>
                <div class="kpi-note">Menunggu: {{ $totalMenunggu }} &bull; Aktif: {{ $totalAktif }}</div>
            </td>
            <td class="kpi-box">
                <div class="kpi-label">Selesai / Dikembalikan</div>
                <div class="kpi-val" style="color: #15803d;">{{ number_format($totalDikembalikan) }}</div>
                <div class="kpi-note">
                    @php $pctSelesai = $totalPeminjaman > 0 ? round(($totalDikembalikan / $totalPeminjaman) * 100) : 0; @endphp
                    Tingkat penyelesaian: {{ $pctSelesai }}%
                </div>
            </td>
            <td class="kpi-box">
                <div class="kpi-label">Bentuk Layanan</div>
                <div class="kpi-val" style="color: #1d4ed8;">{{ number_format($totalFisik + $totalDigital + $totalInformasi) }}</div>
                <div class="kpi-note">Fisik: {{ $totalFisik }} &bull; Digital: {{ $totalDigital }} &bull; Info: {{ $totalInformasi }}</div>
            </td>
            <td class="kpi-box">
                <div class="kpi-label">Terlambat Kembali</div>
                <div class="kpi-val" style="color: #b91c1c;">{{ number_format($totalTerlambat) }}</div>
                <div class="kpi-note">Internal: {{ $peminjamanInternal }} &bull; SKPD: {{ $peminjamanSkpd }} &bull; Kec: {{ $peminjamanKecamatan }} &bull; Eks: {{ $peminjamanEksternal }}</div>
            </td>
        </tr>
    </table>

    <!-- Rekap Bulanan 12 Bulan -->
    <div class="section-title">Rekapitulasi Volume Layanan per Bulan (Tahun {{ $tahun }})</div>
    @php
        $namaBulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    @endphp
    <table class="grid-table">
        <thead>
            <tr>
                @foreach ($namaBulan as $nm)
                    <th>{{ $nm }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach ($namaBulan as $num => $nm)
                    <td class="text-center"><strong>{{ $rekapBulanan[$num] ?? 0 }}</strong></td>
                @endforeach
                <td class="text-center" style="background: #f1f5f9;"><strong>{{ $totalPeminjaman }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Tabel Rincian Surat Peminjaman -->
    <div class="section-title">Rincian Surat Peminjaman &amp; Pengembalian Arsip</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 13%;">Nomor Surat</th>
                <th style="width: 11%;">Bidang</th>
                <th style="width: 8%;">Layanan</th>
                <th style="width: 15%;">Peminjam &amp; Instansi</th>
                <th style="width: 17%;">Maksud / Keperluan</th>
                <th style="width: 6%;">Berkas</th>
                <th style="width: 11%;">Tgl Pinjam - Tempo</th>
                <th style="width: 9%;">Pengembalian</th>
                <th style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarSurat as $idx => $s)
                @php
                    $isTerlambat = $s->isTerlambat();
                    $statusLabel = $isTerlambat ? 'Terlambat' : $s->status;
                    $badgeCls = match (true) {
                        $s->status === 'Selesai' => 'badge-ok',
                        $isTerlambat || in_array($s->status, ['Ditolak', 'Dibatalkan']) => 'badge-err',
                        $s->status === 'Menunggu Persetujuan' => 'badge-warn',
                        default => 'badge-info',
                    };
                    $tglKembaliInfo = match (true) {
                        $s->status === 'Selesai' => $s->tanggal_selesai ? $s->tanggal_selesai->format('d/m/Y') : 'Selesai',
                        $s->status === 'Menunggu Persetujuan' => 'Menunggu',
                        in_array($s->status, ['Ditolak', 'Dibatalkan']) => $s->status,
                        $isTerlambat => 'Belum Kembali',
                        default => $s->isFisik() ? 'Dipinjam' : 'Berjalan',
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $s->nomor_surat }}</strong></td>
                    <td>{{ $s->unitPengolah?->nama ?? '-' }}</td>
                    <td class="text-center">{{ $s->bentuk_layanan ?: 'Fisik' }}</td>
                    <td>
                        <strong>{{ $s->peminjam?->nama ?? '-' }}</strong><br>
                        <span style="color: #475569;">{{ $s->peminjam?->instansi_unit ?? '-' }} ({{ $s->peminjam?->jenisLabel() ?? '-' }})</span>
                    </td>
                    <td>{{ $s->maksud_keperluan ?: '-' }}</td>
                    <td class="text-center">{{ $s->items->count() }}</td>
                    <td class="text-center">
                        {{ $s->tanggal_mulai?->format('d/m/Y') ?? '-' }}<br>
                        s.d. {{ $s->tanggal_kembali?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="text-center">{{ $tglKembaliInfo }}</td>
                    <td class="text-center"><span class="badge {{ $badgeCls }}">{{ $statusLabel }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 14px; color: #64748b;">Tidak ada transaksi peminjaman pada tahun {{ $tahun }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($arsipTerbanyak->isNotEmpty())
        <div class="section-title">10 Berkas Arsip Paling Sering Dipinjam ({{ $tahun }})</div>
        <table class="grid-table">
            <thead>
                <tr>
                    <th style="width: 5%;">Peringkat</th>
                    <th style="width: 18%;">Kode Berkas</th>
                    <th style="width: 52%;">Uraian Berkas Arsip</th>
                    <th style="width: 15%;">Kurun Waktu / Pencipta</th>
                    <th style="width: 10%;">Frekuensi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($arsipTerbanyak as $idx => $at)
                    <tr>
                        <td class="text-center">#{{ $idx + 1 }}</td>
                        <td><strong>{{ $at->arsip?->kode_internal ?? '-' }}</strong></td>
                        <td>{{ $at->arsip?->uraian_berkas ?? '-' }}</td>
                        <td class="text-center">{{ $at->arsip?->kurun_waktu ?: '-' }}</td>
                        <td class="text-center"><strong>{{ $at->total_pinjam }}x</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="ttd-footer">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Dinas / Penanggung Jawab Kearsipan
                <div class="ttd-gap"></div>
                <strong><u>........................................</u></strong><br>
                <span>NIP. ........................................</span>
            </td>
            <td>
                Sumber, {{ now()->translatedFormat('d F Y') }}<br>
                Petugas Pengelola Layanan Arsip
                <div class="ttd-gap"></div>
                <strong><u>{{ $dicetakOleh }}</u></strong>
            </td>
        </tr>
    </table>
</body>
</html>
