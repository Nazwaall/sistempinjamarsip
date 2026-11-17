<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Peminjam - {{ $peminjam->nama }}</title>
    <style>
        @page {
            margin: 1.2cm 1.6cm 1.3cm 1.6cm;
            size: A4 portrait;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.38;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        .kop-table td { border: none; padding: 0; vertical-align: middle; }
        .kop-logo { width: 76px; text-align: left; }
        .kop-logo img { height: 68px; width: auto; display: block; }
        .kop-teks { text-align: center; }
        .kop-teks .instansi-nama { margin: 0; font-size: 12pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; line-height: 1.2; }
        .kop-teks .dinas-nama { margin: 2px 0 3px 0; font-size: 15pt; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; line-height: 1.15; }
        .kop-teks .alamat-line { margin: 0; font-size: 8.8pt; line-height: 1.25; }
        .kop-teks .kontak-line { margin: 1px 0 0 0; font-size: 8.8pt; line-height: 1.25; }
        .kop-teks .kontak-line a { color: #0000ff; text-decoration: underline; }
        .kop-teks .kodepos-line { margin: 1px 0 0 0; font-size: 8.8pt; font-weight: bold; letter-spacing: 0.5px; }
        .kop-spacer { width: 76px; }
        .garis-kop { border-top: 2.5px solid #000; border-bottom: 1px solid #000; height: 2px; margin: 4px 0 12px 0; }

        .judul-laporan { text-align: center; margin-bottom: 12px; }
        .judul-laporan h2 { margin: 0; font-size: 11.5pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .judul-laporan .sub { margin-top: 2px; font-size: 8.5pt; color: #475569; }

        .info-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .info-box td { padding: 4px 8px; font-size: 8.5pt; vertical-align: top; }
        .info-label { width: 19%; color: #475569; font-weight: bold; }
        .info-val { width: 31%; color: #0f172a; }

        .stats-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 12px -6px; }
        .stat-cell {
            width: 33.33%;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            padding: 6px 8px;
            text-align: center;
        }
        .stat-num { font-size: 13pt; font-weight: bold; }
        .stat-lbl { font-size: 7.5pt; color: #475569; text-transform: uppercase; }

        table.grid-table { width: 100%; border-collapse: collapse; }
        table.grid-table thead { display: table-header-group; }
        table.grid-table tr { page-break-inside: avoid; }
        table.grid-table th,
        table.grid-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            vertical-align: top;
            font-size: 8pt;
        }
        table.grid-table th { background: #e2e8f0; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .ttd-footer { width: 100%; border-collapse: collapse; margin-top: 16px; page-break-inside: avoid; }
        .ttd-footer td { width: 55%; font-size: 8pt; color: #475569; vertical-align: top; }
        .ttd-footer td.ttd-kanan { width: 45%; text-align: center; color: #0f172a; font-size: 8.5pt; }
        .ttd-gap { height: 48px; }
    </style>
</head>
<body>
    @include('partials.pdf-kop', ['pengaturan' => $pengaturan])

    <div class="judul-laporan">
        <h2>Profil &amp; Riwayat Peminjaman Arsip</h2>
        <div class="sub">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>

    <table class="info-box">
        <tr>
            <td class="info-label">Nama Lengkap</td>
            <td class="info-val">: <strong>{{ $peminjam->nama }}</strong></td>
            <td class="info-label">Kategori</td>
            <td class="info-val">: {{ $peminjam->jenisLabel() }}</td>
        </tr>
        <tr>
            <td class="info-label">NIP / NIK</td>
            <td class="info-val">: {{ $peminjam->nip_nik ?: '-' }}</td>
            <td class="info-label">Jabatan</td>
            <td class="info-val">: {{ $peminjam->jabatan ?: '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Instansi / Unit</td>
            <td class="info-val">: {{ $peminjam->instansi_unit }}</td>
            <td class="info-label">No. HP / WA</td>
            <td class="info-val">: {{ $peminjam->no_hp }}</td>
        </tr>
        <tr>
            <td class="info-label">Jaminan</td>
            <td class="info-val">: {{ $peminjam->jaminan ?: '-' }}</td>
            <td class="info-label">Email</td>
            <td class="info-val">: {{ $peminjam->email ?: '-' }}</td>
        </tr>
        @if ($peminjam->alamat)
            <tr>
                <td class="info-label">Alamat</td>
                <td colspan="3" class="info-val">: {{ $peminjam->alamat }}</td>
            </tr>
        @endif
    </table>

    <table class="stats-table">
        <tr>
            <td class="stat-cell">
                <div class="stat-num" style="color: #1d4ed8;">{{ $totalSurat }}</div>
                <div class="stat-lbl">Total Surat</div>
            </td>
            <td class="stat-cell">
                <div class="stat-num" style="color: #b45309;">{{ $suratAktif }}</div>
                <div class="stat-lbl">Sedang Dipinjam</div>
            </td>
            <td class="stat-cell">
                <div class="stat-num" style="color: #15803d;">{{ $suratSelesai }}</div>
                <div class="stat-lbl">Telah Selesai</div>
            </td>
        </tr>
    </table>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 21%;">Nomor &amp; Tanggal Surat</th>
                <th style="width: 18%;">Bidang &amp; Layanan</th>
                <th style="width: 24%;">Rentang Peminjaman</th>
                <th style="width: 19%;">Daftar Berkas</th>
                <th style="width: 13%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peminjam->suratPeminjaman as $idx => $surat)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $surat->nomor_surat }}</strong><br>
                        <span style="color: #475569;">{{ $surat->tanggal_surat->translatedFormat('d M Y') }}</span>
                    </td>
                    <td>
                        {{ $surat->unitPengolah?->nama ?? '-' }}<br>
                        <span style="color: #475569;">{{ $surat->bentuk_layanan ?: 'Fisik' }}</span>
                    </td>
                    <td class="text-center">
                        {{ $surat->tanggal_mulai->format('d/m/Y') }} s.d. {{ $surat->tanggal_kembali->format('d/m/Y') }}<br>
                        <span style="color: #475569;">({{ $surat->lama_pinjam_hari }} hari)</span>
                    </td>
                    <td>
                        <strong>{{ $surat->items->count() }} berkas</strong>
                        @if ($surat->items->isNotEmpty())
                            <br><span style="color: #475569;">{{ $surat->items->first()?->arsip?->kode_internal }}</span>
                        @endif
                    </td>
                    <td class="text-center"><strong>{{ $surat->isTerlambat() ? 'Terlambat' : $surat->status }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 14px; color: #64748b;">Peminjam ini belum memiliki riwayat surat peminjaman.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd-footer">
        <tr>
            <td>Dokumen ini dicetak dari Sistem Informasi Peminjaman Arsip (SIPA).</td>
            <td class="ttd-kanan">
                Sumber, {{ now()->translatedFormat('d F Y') }}<br>
                Petugas Layanan Arsip
                <div class="ttd-gap"></div>
                <strong><u>{{ $dicetakOleh }}</u></strong>
            </td>
        </tr>
    </table>
</body>
</html>
