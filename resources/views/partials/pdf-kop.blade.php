@php
    $logoPath = public_path('images/logo-cirebon.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    $websiteClean = str_replace(['https://', 'http://'], '', $pengaturan['website_dinas'] ?? 'www.disarpus.cirebonkab.go.id');
    $emailDinas = $pengaturan['email_dinas'] ?? 'disarpus@cirebonkab.go.id';
@endphp

<table class="kop-table">
    <tr>
        <td class="kop-logo">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Lambang Kabupaten Cirebon">
            @endif
        </td>
        <td class="kop-teks">
            <div class="instansi-nama">{{ $pengaturan['kop_baris_1'] ?? ($pengaturan['instansi_nama'] ?? 'PEMERINTAH KABUPATEN CIREBON') }}</div>
            <div class="dinas-nama">{{ $pengaturan['kop_baris_2'] ?? ($pengaturan['dinas_nama'] ?? 'DINAS KEARSIPAN DAN PERPUSTAKAAN') }}</div>
            <div class="alamat-line">{{ $pengaturan['alamat_dinas'] ?? 'Jalan Sunan Kalijaga No. 1  Telp (0231) 323234 Fax. 323234' }}</div>
            <div class="kontak-line">website: <a href="http://{{ $websiteClean }}">{{ $websiteClean }}</a> &nbsp; email: <a href="mailto:{{ $emailDinas }}">{{ $emailDinas }}</a></div>
            <div class="kodepos-line">SUMBER - 45611</div>
        </td>
        <td class="kop-spacer"></td>
    </tr>
</table>
<div class="garis-kop"></div>
