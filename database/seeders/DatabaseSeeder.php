<?php

namespace Database\Seeders;

use App\Models\UnitPengolah;
use App\Models\User;
use App\Models\Pengaturan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Unit Pengolah (Bidang)
        $bidangArsip = UnitPengolah::firstOrCreate(
            ['kode' => 'ARS'],
            [
                'nama' => 'Bidang Pengelolaan Arsip',
                'kepala_nama' => 'H. Ahmad Supriyadi, S.AP',
                'kepala_nip' => '197508122005011002',
                'lama_pinjam_standar' => 7,
                'lama_pinjam_maks' => 7,
                'aktif' => true,
            ]
        );

        $bidangSekretariat = UnitPengolah::firstOrCreate(
            ['kode' => 'SEK'],
            [
                'nama' => 'Sekretariat',
                'kepala_nama' => 'Dra. Hj. Sri Wahyuni, M.Si',
                'kepala_nip' => '197204151998032004',
                'lama_pinjam_standar' => 7,
                'lama_pinjam_maks' => 7,
                'aktif' => true,
            ]
        );

        $bidangPerpus = UnitPengolah::firstOrCreate(
            ['kode' => 'PERP'],
            [
                'nama' => 'Bidang Perpustakaan',
                'kepala_nama' => 'Drs. H. Maman Suratman',
                'kepala_nip' => '196911201994031003',
                'lama_pinjam_standar' => 7,
                'lama_pinjam_maks' => 7,
                'aktif' => true,
            ]
        );

        $bidangP2a = UnitPengolah::firstOrCreate(
            ['kode' => 'P2A'],
            [
                'nama' => 'Bidang P2A',
                'kepala_nama' => 'Kepala Bidang P2A',
                'kepala_nip' => null,
                'lama_pinjam_standar' => 7,
                'lama_pinjam_maks' => 7,
                'aktif' => true,
            ]
        );

        // 2. Akun Pengguna Awal
        // Super Admin
        User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'unit_pengolah_id' => null,
                'name' => 'Super Admin SIPA',
                'nip' => '198001012006041001',
                'email' => 'superadmin.sipa@gmail.com',
                'password' => Hash::make('admin123'),
                'peran' => 'SuperAdmin',
                'aktif' => true,
            ]
        );

        // Operator Bidang Arsip
        User::firstOrCreate(
            ['username' => 'operator.arsip'],
            [
                'unit_pengolah_id' => $bidangArsip->id,
                'name' => 'Petugas Arsip Dinamis',
                'nip' => '198805202010012003',
                'email' => 'operator.arsip@gmail.com',
                'password' => Hash::make('operator123'),
                'peran' => 'Operator',
                'aktif' => true,
            ]
        );

        // 3. Pengaturan Awal
        $pengaturanAwal = [
            'instansi_nama' => 'PEMERINTAH KABUPATEN CIREBON',
            'dinas_nama' => 'DINAS KEARSIPAN DAN PERPUSTAKAAN',
            'dinas_alamat' => 'Jalan Sunan Kalijaga No. 1  Telp (0231) 323234 Fax. 323234',
            'dinas_website' => 'www.disarpus.cirebonkab.go.id',
            'dinas_email' => 'disarpus@cirebonkab.go.id',
            'dinas_kodepos' => 'SUMBER - 45611',
            'format_nomor_surat' => '{URUT}/SPA/{KODE_BIDANG}/{BULAN_ROMAWI}/{TAHUN}',
            'lama_pinjam_default' => '7',
            'pernyataan_peminjaman' => 'Saya berjanji menjaga keutuhan, kerahasiaan, dan keamanan arsip fisik yang dipinjam, tidak menggandakan atau meminjamkannya ke pihak lain tanpa izin sah, dan wajib mengembalikan tepat waktu sesuai rentang peminjaman. Saya bertanggung jawab penuh bila arsip hilang atau rusak.',
        ];

        foreach ($pengaturanAwal as $kunci => $nilai) {
            Pengaturan::firstOrCreate(
                ['kunci' => $kunci],
                ['nilai' => $nilai]
            );
        }
    }
}
