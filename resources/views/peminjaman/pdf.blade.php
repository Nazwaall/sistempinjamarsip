<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Peminjaman Arsip - {{ $peminjaman->nomor_surat }}</title>
    <style>
        @page {
            margin: 1.2cm 2cm 1.2cm 2cm;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* --- KOP DINAS RESMI PEMERINTAH KABUPATEN CIREBON --- */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 2px;
        }
        .kop-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .kop-logo {
            width: 85px;
            text-align: left;
        }
        .kop-logo img {
            height: 78px;
            width: auto;
            display: block;
        }
        .kop-teks {
            text-align: center;
        }
        .kop-teks .instansi-nama {
            margin: 0;
            font-size: 13.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .kop-teks .dinas-nama {
            margin: 2px 0 3px 0;
            font-size: 17pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .kop-teks .alamat-line {
            margin: 0;
            font-size: 9.5pt;
            line-height: 1.3;
        }
        .kop-teks .kontak-line {
            margin: 1px 0 0 0;
            font-size: 9.5pt;
            line-height: 1.3;
        }
        .kop-teks .kontak-line a {
            color: #0000ff;
            text-decoration: underline;
        }
        .kop-teks .kodepos-line {
            margin: 1px 0 0 0;
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-spacer {
            width: 85px;
        }
        .garis-kop {
            border-top: 2.5px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 4px 0 16px 0;
        }

        /* --- JUDUL & NOMOR SURAT RESMI --- */
        .judul-surat {
            text-align: center;
            margin-bottom: 16px;
        }
        .judul-surat .judul-teks {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
        }
        .judul-surat .nomor-teks {
            font-size: 10pt;
            margin-top: 3px;
        }

        /* --- PARAGRAF PEMBUKA & PENUTUP --- */
        .paragraf-surat {
            font-size: 10pt;
            margin: 0 0 8px 0;
            text-align: justify;
            line-height: 1.4;
        }

        /* --- TABEL IDENTITAS PEMINJAM (PRESISI & RAPI) --- */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .meta-table td {
            padding: 2.5px 0;
            vertical-align: top;
            border: none;
            line-height: 1.35;
        }
        .meta-label {
            width: 170px;
            color: #000;
        }
        .meta-colon {
            width: 15px;
            text-align: left;
        }
        .meta-val {
            color: #000;
        }

        /* --- TABEL DAFTAR BERKAS ARSIP --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 6px 0 12px 0;
            font-size: 9pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
        }
        .data-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }
        .data-table td {
            vertical-align: top;
        }

        /* --- KETENTUAN PEMINJAMAN --- */
        .ketentuan-box {
            font-size: 9pt;
            line-height: 1.35;
            margin: 10px 0 12px 0;
            text-align: justify;
        }
        .ketentuan-box ol {
            margin: 3px 0 0 0;
            padding-left: 20px;
        }
        .ketentuan-box li {
            margin-bottom: 2px;
        }

        /* --- BLOK TANDA TANGAN 3 PIHAK STANDAR NASKAH DINAS --- */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
            font-size: 9.5pt;
        }
        .ttd-table td {
            border: none;
            padding: 0;
            text-align: center;
            width: 33.33%;
        }
        .ttd-titimangsa {
            padding-bottom: 2px;
            text-align: center;
        }
        .ttd-jabatan {
            height: 38px;
            vertical-align: top;
            padding-bottom: 2px;
            line-height: 1.25;
        }
        .ttd-space {
            height: 55px;
            vertical-align: middle;
        }
        .ttd-space img {
            max-height: 50px;
            max-width: 140px;
            width: auto;
            margin: 0 auto;
            display: block;
        }
        .ttd-nama {
            vertical-align: top;
            padding-top: 2px;
            line-height: 1.25;
        }
        .ttd-nama strong {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- KOP DINAS RESMI -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @php
                    $logoPath = public_path('images/logo-cirebon.png');
                    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
                @endphp
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Lambang Kabupaten Cirebon">
                @endif
            </td>
            <td class="kop-teks">
                <div class="instansi-nama">{{ $pengaturan['kop_baris_1'] ?? ($pengaturan['instansi_nama'] ?? 'PEMERINTAH KABUPATEN CIREBON') }}</div>
                <div class="dinas-nama">{{ $pengaturan['kop_baris_2'] ?? ($pengaturan['dinas_nama'] ?? 'DINAS KEARSIPAN DAN PERPUSTAKAAN') }}</div>
                <div class="alamat-line">{{ $pengaturan['alamat_dinas'] ?? 'Jalan Sunan Kalijaga No. 1  Telp (0231) 323234 Fax. 323234' }}</div>
                <div class="kontak-line">website: <a href="http://www.disarpus.cirebonkab.go.id">{{ str_replace(['https://', 'http://'], '', $pengaturan['website_dinas'] ?? 'www.disarpus.cirebonkab.go.id') }}</a> &nbsp;email: <a href="mailto:{{ $pengaturan['email_dinas'] ?? 'disarpus@cirebonkab.go.id' }}">{{ $pengaturan['email_dinas'] ?? 'disarpus@cirebonkab.go.id' }}</a></div>
                <div class="kodepos-line">SUMBER - 45611</div>
            </td>
            <td class="kop-spacer"></td>
        </tr>
    </table>
    
    <!-- GARIS PEMBATAS KOP DINAS GANDA -->
    <div class="garis-kop"></div>

    @php
        $layanan = $peminjaman->bentuk_layanan ?: 'Fisik';
        $judulSurat = match($layanan) {
            'Digital (PDF)' => 'SURAT BUKTI LAYANAN ARSIP DIGITAL (PDF)',
            'Layanan Informasi', 'Layanan Informasi (Statis)' => 'SURAT BUKTI LAYANAN INFORMASI ARSIP',
            default => 'SURAT BUKTI PEMINJAMAN ARSIP',
        };
    @endphp

    <!-- JUDUL & NOMOR SURAT RESMI -->
    <div class="judul-surat">
        <div class="judul-teks">{{ $judulSurat }}</div>
        <div class="nomor-teks">Nomor : {{ $peminjaman->nomor_surat }}</div>
    </div>

    <!-- KALIMAT PEMBUKA RESMI -->
    <p class="paragraf-surat">Yang bertanda tangan di bawah ini menerangkan bahwa:</p>

    <!-- IDENTITAS PEMINJAM (PRESISI & RAPI) -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Bidang Pembuat Surat</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->unitPengolah->nama }} ({{ $peminjaman->unitPengolah->kode }})</td>
        </tr>
        <tr>
            <td class="meta-label">Bentuk Layanan</td>
            <td class="meta-colon">:</td>
            <td class="meta-val"><strong>{{ $layanan }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Nama Peminjam</td>
            <td class="meta-colon">:</td>
            <td class="meta-val"><strong>{{ $peminjaman->peminjam->nama }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">NIP / NIK</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->peminjam->nip_nik ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Jabatan</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->peminjam->jabatan ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Instansi / Kategori</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->peminjam->instansi_unit }} ({{ $peminjaman->peminjam->jenisLabel() }})</td>
        </tr>
        <tr>
            <td class="meta-label">Nomor Telepon / HP</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->peminjam->no_hp }}</td>
        </tr>
        @if ($peminjaman->peminjam->alamat)
        <tr>
            <td class="meta-label">Alamat</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->peminjam->alamat }}</td>
        </tr>
        @endif
        <tr>
            <td class="meta-label">Jaminan</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->jaminan ?: ($peminjaman->peminjam->jaminan ?: '-') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Maksud / Keperluan</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->maksud_keperluan }}</td>
        </tr>
        @if ($peminjaman->is_janjian && $peminjaman->rencana_ambil_pada)
        <tr>
            <td class="meta-label">Jadwal Janjian</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $peminjaman->rencana_ambil_pada->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        @endif
        <tr>
            <td class="meta-label">{{ $peminjaman->isFisik() ? 'Rentang Peminjaman' : 'Masa Layanan' }}</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">
                <strong>{{ \Carbon\Carbon::parse($peminjaman->tanggal_mulai)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->translatedFormat('d F Y') }} ({{ $peminjaman->lama_pinjam_hari }} hari)</strong>
            </td>
        </tr>
    </table>

    <!-- KALIMAT PENGANTAR DAFTAR ARSIP -->
    @if ($layanan === 'Digital (PDF)')
        <p class="paragraf-surat">Telah menerima salinan digital (PDF) berkas arsip yang diterbitkan melalui <strong>{{ $peminjaman->unitPengolah->nama }}</strong> sebagai berikut:</p>
    @elseif (in_array($layanan, ['Layanan Informasi', 'Layanan Informasi (Statis)']))
        <p class="paragraf-surat">Telah memperoleh layanan informasi / baca di tempat atas arsip yang dikelola melalui <strong>{{ $peminjaman->unitPengolah->nama }}</strong> sebagai berikut:</p>
    @else
        <p class="paragraf-surat">Telah menerima fisik berkas arsip melalui <strong>{{ $peminjaman->unitPengolah->nama }}</strong> sebagai berikut:</p>
    @endif

    <!-- TABEL DAFTAR ARSIP -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 19%;">Kode Berkas</th>
                <th style="width: 36%;">Uraian Berkas Arsip</th>
                <th style="width: 18%;">Bidang / Pencipta Asal</th>
                <th style="width: 8%;">Tahun</th>
                <th style="width: 14%;">Lokasi Simpan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($peminjaman->items as $index => $item)
                <tr>
                    <td style="text-align: center; vertical-align: middle;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->arsip->kode_internal }}</strong>
                        @if($item->arsip->kode_klasifikasi)
                            <br><small style="color: #444;">{{ $item->arsip->kode_klasifikasi }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $item->arsip->uraian_berkas }}
                        @if($item->arsip->uraian_informasi)
                            <br><small style="color: #444;">{{ \Illuminate\Support\Str::limit($item->arsip->uraian_informasi, 110) }}</small>
                        @endif
                    </td>
                    <td>{{ $item->arsip->unit_pengolah_asal ?: ($item->arsip->unitPengolah?->nama ?? '-') }}</td>
                    <td style="text-align: center; vertical-align: middle;">{{ $item->arsip->tahun }}</td>
                    <td style="text-align: center; vertical-align: middle;">{{ $item->arsip->lokasi_simpan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- KETENTUAN & KLAUSUL TANGGUNG JAWAB RESMI -->
    <div class="ketentuan-box">
        @if ($layanan === 'Digital (PDF)')
            <strong>Ketentuan Layanan Digital (PDF):</strong>
            <ol>
                <li>Salinan digital (PDF) hanya dipergunakan sesuai maksud dan keperluan resmi yang tercantum dalam surat ini.</li>
                <li>Peminjam wajib menjaga kerahasiaan data/informasi arsip dan dilarang menyebarluaskan atau memodifikasi isi tanpa izin resmi.</li>
                <li>Arsip fisik asli tetap tersimpan aman di unit pengolah / depo Dinas Kearsipan dan Perpustakaan Kabupaten Cirebon.</li>
            </ol>
        @elseif (in_array($layanan, ['Layanan Informasi', 'Layanan Informasi (Statis)']))
            <strong>Ketentuan Layanan Informasi Arsip:</strong>
            <ol>
                <li>Akses informasi arsip hanya dilaksanakan di tempat layanan baca/penelitian yang ditetapkan tanpa membawa keluar berkas fisik asli.</li>
                <li>Pengguna layanan wajib menjaga keutuhan dokumen dan mencantumkan sumber resmi Dinas Kearsipan dan Perpustakaan Kabupaten Cirebon apabila dikutip.</li>
                <li>Penggandaan sebagian/seluruh arsip harus mengikuti prosedur perizinan dan ketentuan kearsipan yang berlaku.</li>
            </ol>
        @else
            <strong>Ketentuan Peminjaman:</strong>
            <ol>
                <li>Peminjam bertanggung jawab penuh atas keutuhan, kerahasiaan, dan keamanan fisik berkas arsip yang dipinjam.</li>
                <li>Berkas arsip tidak diperkenankan untuk digandakan atau dialihpinjamkan kepada pihak lain tanpa izin tertulis yang sah.</li>
                <li>Berkas arsip wajib dikembalikan dalam keadaan lengkap dan baik selambat-lambatnya pada tanggal jatuh tempo peminjaman di atas.</li>
            </ol>
        @endif
    </div>

    <!-- KALIMAT PENUTUP RESMI -->
    <p class="paragraf-surat" style="margin-top: 6px; margin-bottom: 10px;">
        Demikian surat bukti {{ $peminjaman->isFisik() ? 'peminjaman' : 'layanan' }} arsip ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    <!-- TANDA TANGAN 3 PIHAK: KIRI (PEMINJAM), TENGAH (MENGETAHUI), KANAN (PETUGAS PENYERAH) -->
    <table class="ttd-table">
        <!-- Baris Titimangsa: Sejajar di Kolom Petugas Penyerah (Kanan) -->
        <tr>
            <td style="width: 33.33%;">&nbsp;</td>
            <td style="width: 33.34%;">&nbsp;</td>
            <td style="width: 33.33%;" class="ttd-titimangsa">
                Sumber, {{ \Carbon\Carbon::parse($peminjaman->tanggal_surat)->translatedFormat('d F Y') }}
            </td>
        </tr>

        @php
            $namaPetugasLayanan = $peminjaman->namaPetugasLayanan();
            $nipPetugasLayanan = $peminjaman->nipPetugasLayanan();
            $bidangPetugasLayanan = $peminjaman->bidangPetugasLayanan();
        @endphp

        <!-- Baris Jabatan / Peran: Kiri (Peminjam), Tengah (Mengetahui), Kanan (Petugas Penyerah) -->
        <tr>
            <td class="ttd-jabatan">
                Peminjam,
            </td>
            <td class="ttd-jabatan">
                Mengetahui,<br>
                Kepala {{ $peminjaman->unitPengolah->nama }} (Kabid)
            </td>
            <td class="ttd-jabatan">
                Petugas Layanan,<br>
                @if ($bidangPetugasLayanan)
                    <span style="font-size: 8.5pt;">{{ $bidangPetugasLayanan }}</span>
                @endif
            </td>
        </tr>

        <!-- Baris Ruang Tanda Tangan (Tinggi Seragam 55px, Sejajar Sempurna) -->
        <tr>
            <td class="ttd-space">
                @php
                    $ttdBase64 = null;
                    if ($peminjaman->metode_ttd === 'Digital' && $peminjaman->ttd_peminjam_path) {
                        $p1 = storage_path('app/public/' . $peminjaman->ttd_peminjam_path);
                        $p2 = public_path('storage/' . $peminjaman->ttd_peminjam_path);
                        $foundPath = file_exists($p1) ? $p1 : (file_exists($p2) ? $p2 : null);
                        if ($foundPath) {
                            $content = @file_get_contents($foundPath);
                            if ($content !== false && strlen($content) > 0) {
                                $ttdBase64 = 'data:image/png;base64,' . base64_encode($content);
                            }
                        }
                    }
                @endphp
                @if ($ttdBase64)
                    <img src="{{ $ttdBase64 }}" alt="TTD Digital Peminjam">
                @else
                    &nbsp;
                @endif
            </td>
            <td class="ttd-space">&nbsp;</td>
            <td class="ttd-space">&nbsp;</td>
        </tr>

        <!-- Baris Nama Lengkap & NIP (Rata Sempurna Satu Garis Horisontal) -->
        <tr>
            <td class="ttd-nama">
                <strong><u>{{ $peminjaman->peminjam->nama }}</u></strong><br>
                <small>{{ $peminjaman->peminjam->nip_nik ? 'NIP/NIK: ' . $peminjaman->peminjam->nip_nik : '-' }}</small>
            </td>
            <td class="ttd-nama">
                <strong><u>{{ $peminjaman->unitPengolah->kepala_nama ?: ($peminjaman->disetujuiOleh?->name ?? 'Kepala Bidang (Kabid)') }}</u></strong><br>
                <small>{{ $peminjaman->unitPengolah->kepala_nip ? 'NIP. ' . $peminjaman->unitPengolah->kepala_nip : '-' }}</small>
            </td>
            <td class="ttd-nama">
                <strong><u>{{ $namaPetugasLayanan }}</u></strong><br>
                <small>{{ $nipPetugasLayanan ? 'NIP. ' . $nipPetugasLayanan : 'NIP. -' }}</small>
            </td>
        </tr>
    </table>

</body>
</html>
