<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Direktori Peminjam Arsip</title>
    <style>
        @page {
            margin: 1.1cm 1.4cm 1.2cm 1.4cm;
            size: A4 landscape;
        }
        * { box-sizing: border-box; }
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

        .meta-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .meta-bar td {
            padding: 6px 10px;
            font-size: 8pt;
            border-right: 1px solid #e2e8f0;
        }
        .meta-bar td:last-child { border-right: none; }

        table.grid-table { width: 100%; border-collapse: collapse; }
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
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
        }
        .badge-aktif { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .ttd-footer { width: 100%; border-collapse: collapse; margin-top: 14px; page-break-inside: avoid; }
        .ttd-footer td { width: 65%; text-align: left; vertical-align: top; font-size: 8pt; color: #475569; }
        .ttd-footer td.ttd-kanan { width: 35%; text-align: center; color: #0f172a; font-size: 8.5pt; }
        .ttd-gap { height: 46px; }
    </style>
</head>
<body>
    @include('partials.pdf-kop', ['pengaturan' => $pengaturan])

    <div class="judul-laporan">
        <h2>Daftar Direktori Peminjam Arsip</h2>
        <div class="sub">
            Kategori: <strong>{{ $jenisDipilih }}</strong>
            @if ($kataKunci !== '')
                &nbsp;|&nbsp; Pencarian: <strong>"{{ $kataKunci }}"</strong>
            @endif
            &nbsp;|&nbsp; Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

    <table class="meta-bar">
        <tr>
            <td><strong>Total Data:</strong> {{ $peminjamList->count() }} peminjam</td>
            <td><strong>Internal:</strong> {{ $peminjamList->where('jenis', 'Internal')->count() }}</td>
            <td><strong>SKPD:</strong> {{ $peminjamList->where('jenis', 'SKPD')->count() }}</td>
            <td><strong>Kecamatan / Kel:</strong> {{ $peminjamList->where('jenis', 'Kecamatan / Kelurahan')->count() }}</td>
            <td><strong>Eksternal:</strong> {{ $peminjamList->where('jenis', 'Eksternal')->count() }}</td>
            <td><strong>Sedang Pinjam:</strong> {{ $peminjamList->where('pinjaman_aktif_count', '>', 0)->count() }} peminjam</td>
        </tr>
    </table>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 17%;">Nama Peminjam &amp; Jabatan</th>
                <th style="width: 11%;">Kategori</th>
                <th style="width: 12%;">NIP / NIK</th>
                <th style="width: 21%;">Instansi / Unit &amp; Alamat</th>
                <th style="width: 15%;">Kontak (HP &amp; Email)</th>
                <th style="width: 10%;">Jaminan</th>
                <th style="width: 6%;">Total Surat</th>
                <th style="width: 5%;">Aktif</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peminjamList as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $p->nama }}</strong>
                        @if ($p->jabatan)
                            <br><span style="color: #475569;">{{ $p->jabatan }}</span>
                        @endif
                    </td>
                    <td class="text-center"><span class="badge">{{ $p->jenisLabel() }}</span></td>
                    <td>{{ $p->nip_nik ?: '-' }}</td>
                    <td>
                        <strong>{{ $p->instansi_unit }}</strong>
                        @if ($p->alamat)
                            <br><span style="color: #475569;">{{ $p->alamat }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $p->no_hp }}
                        @if ($p->email)
                            <br><span style="color: #475569;">{{ $p->email }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $p->jaminan ?: '-' }}</td>
                    <td class="text-center"><strong>{{ $p->surat_peminjaman_count }}</strong></td>
                    <td class="text-center">
                        @if ($p->pinjaman_aktif_count > 0)
                            <span class="badge badge-aktif">{{ $p->pinjaman_aktif_count }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 14px; color: #64748b;">Tidak ada data peminjam sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd-footer">
        <tr>
            <td>
                Dokumen dihasilkan otomatis oleh Sistem Informasi Peminjaman Arsip (SIPA).
            </td>
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
