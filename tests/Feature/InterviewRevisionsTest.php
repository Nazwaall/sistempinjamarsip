<?php

namespace Tests\Feature;

use App\Http\Controllers\LaporanController;
use App\Models\Arsip;
use App\Models\Peminjam;
use App\Models\Pengaturan;
use App\Models\SuratPeminjaman;
use App\Models\UnitPengolah;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class InterviewRevisionsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $operator;
    protected UnitPengolah $unitArs;
    protected UnitPengolah $unitSek;

    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::where('peran', 'SuperAdmin')->firstOrFail();
        $this->operator = User::where('peran', 'Operator')->firstOrFail();
        $this->unitArs = UnitPengolah::where('kode', 'ARS')->firstOrFail();
        $this->unitSek = UnitPengolah::where('kode', 'SEK')->firstOrFail();
    }

    public function test_poin1_arsip_crud_asal_pencipta_depo_dan_impor_excel(): void
    {
        // Halaman create menampilkan field Unit Pengolah Asal & placeholder Depo tanpa dropdown klasifikasi
        $this->actingAs($this->operator)
            ->get(route('arsip.create'))
            ->assertOk()
            ->assertSee('unit_pengolah_asal')
            ->assertSee('Depo A')
            ->assertDontSee('name="kategori_arsip"', false)
            ->assertDontSee('name="jenis_naskah"', false)
            ->assertDontSee('name="sumber_penciptaan"', false);

        // Simpan arsip pertama
        $respArsip1 = $this->actingAs($this->operator)->post(route('arsip.store'), [
            'unit_pengolah_asal' => 'Pemerintah Kabupaten Cirebon',
            'kode_klasifikasi' => '400.1',
            'uraian_berkas' => 'Arsip Sejarah Gedung Arsip Daerah',
            'uraian_informasi' => 'Dokumentasi pendirian depot sejarah',
            'tahun' => 1998,
            'lokasi_simpan' => 'Depo A / UKD-01',
            'klasifikasi_keamanan' => 'Biasa/Terbuka',
        ]);
        $respArsip1->assertRedirect(route('arsip.index'));

        // Simpan arsip kedua
        $this->actingAs($this->operator)->post(route('arsip.store'), [
            'unit_pengolah_asal' => 'Diskominfo Kabupaten Cirebon',
            'kode_klasifikasi' => '000.1',
            'uraian_berkas' => 'Nota Dinas Koordinasi Daerah',
            'tahun' => 2026,
            'lokasi_simpan' => 'UK-02 / Lemari A',
            'klasifikasi_keamanan' => 'Biasa/Terbuka',
        ])->assertRedirect(route('arsip.index'));

        $arsip1 = Arsip::where('uraian_berkas', 'Arsip Sejarah Gedung Arsip Daerah')->firstOrFail();
        $arsip2 = Arsip::where('uraian_berkas', 'Nota Dinas Koordinasi Daerah')->firstOrFail();

        $this->assertSame('Pemerintah Kabupaten Cirebon', $arsip1->unit_pengolah_asal);
        $this->assertSame('Depo A / UKD-01', $arsip1->lokasi_simpan);

        // Edit asal & lokasi simpan
        $this->actingAs($this->operator)->put(route('arsip.update', $arsip2), [
            'unit_pengolah_asal' => 'Sekretariat Daerah Kab. Cirebon',
            'uraian_berkas' => 'Nota Dinas Koordinasi Daerah',
            'tahun' => 2026,
            'kode_klasifikasi' => '000.2',
            'lokasi_simpan' => 'Depo A / Rak 1',
            'klasifikasi_keamanan' => 'Terbatas',
            'status_ketersediaan' => 'Tersedia',
        ])->assertRedirect(route('arsip.index'));

        $arsip2->refresh();
        $this->assertSame('Sekretariat Daerah Kab. Cirebon', $arsip2->unit_pengolah_asal);
        $this->assertSame('000.2', $arsip2->kode_klasifikasi);

        // Smoke check halaman show, edit, dan import arsip
        $this->actingAs($this->operator)->get(route('arsip.show', $arsip1))->assertOk()->assertSee('Pemerintah Kabupaten Cirebon');
        $this->actingAs($this->operator)->get(route('arsip.edit', $arsip1))->assertOk();
        $this->actingAs($this->operator)->get(route('arsip.import'))->assertOk();

        // Filter daftar arsip berdasarkan pencipta asal
        $this->actingAs($this->operator)
            ->get(route('arsip.index', [
                'q' => 'Pemerintah Kabupaten Cirebon',
            ]))
            ->assertOk()
            ->assertSee('Arsip Sejarah Gedung Arsip Daerah')
            ->assertDontSee('Nota Dinas Koordinasi Daerah');

        // Impor Excel dengan kolom Unit Pengolah Asal & Lokasi Simpan
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Uraian Berkas', 'Tahun', 'Unit Pengolah Asal', 'Lokasi Simpan'],
            ['Surat Tugas Pengawasan SKPD', 2026, 'Inspektorat Daerah', 'Depo A / Rak 3'],
        ]);
        $tmpPath = tempnam(sys_get_temp_dir(), 'sipa_imp_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        try {
            $file = new UploadedFile($tmpPath, 'impor_revisi.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
            $this->actingAs($this->operator)->post(route('arsip.import.process'), [
                'file_arsip' => $file,
                'duplikat' => 'semua',
            ])->assertRedirect(route('arsip.index'));
        } finally {
            @unlink($tmpPath);
        }

        $imported = Arsip::where('uraian_berkas', 'Surat Tugas Pengawasan SKPD')->firstOrFail();
        $this->assertSame('Inspektorat Daerah', $imported->unit_pengolah_asal);
        $this->assertSame('Depo A / Rak 3', $imported->lokasi_simpan);
    }

    public function test_poin2_layanan_fisik_digital_dan_informasi_manual(): void
    {
        $arsip1 = Arsip::create([
            'unit_pengolah_id' => $this->unitArs->id,
            'unit_pengolah_asal' => 'Bagian Hukum Setda',
            'kode_internal' => 'ARS-1985-00-9901',
            'uraian_berkas' => 'Arsip Perda Tahun 1985',
            'tahun' => 1985,
            'klasifikasi_keamanan' => 'Biasa/Terbuka',
            'status_ketersediaan' => 'Tersedia',
            'aktif' => true,
        ]);

        $arsip2 = Arsip::create([
            'unit_pengolah_id' => $this->unitArs->id,
            'unit_pengolah_asal' => 'Bidang Pengelolaan Arsip',
            'kode_internal' => 'ARS-2025-00-9902',
            'uraian_berkas' => 'Berkas Pengawasan Kearsipan',
            'tahun' => 2025,
            'klasifikasi_keamanan' => 'Biasa/Terbuka',
            'status_ketersediaan' => 'Tersedia',
            'aktif' => true,
        ]);

        $arsip3 = Arsip::create([
            'unit_pengolah_id' => $this->unitArs->id,
            'unit_pengolah_asal' => 'Sekretariat Dinas',
            'kode_internal' => 'ARS-2026-00-9905',
            'uraian_berkas' => 'Berkas Fisik Tata Usaha',
            'tahun' => 2026,
            'klasifikasi_keamanan' => 'Biasa/Terbuka',
            'status_ketersediaan' => 'Tersedia',
            'aktif' => true,
        ]);

        $peminjam = Peminjam::firstOrFail();

        // 1. Peminjaman Fisik biasa -> mengunci status_ketersediaan = Dipinjam setelah disetujui
        $this->actingAs($this->operator)->post(route('peminjaman.store'), [
            'unit_pengolah_id' => $this->unitArs->id,
            'arsip_ids' => [$arsip3->id],
            'bentuk_layanan' => 'Fisik',
            'tipe_peminjam' => 'terdaftar',
            'peminjam_id' => $peminjam->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_kembali' => now()->addDays(2)->toDateString(),
            'maksud_keperluan' => 'Peminjaman berkas fisik untuk verifikasi',
            'metode_ttd' => 'Manual',
        ])->assertSessionHasNoErrors();

        $suratFisik = SuratPeminjaman::where('maksud_keperluan', 'Peminjaman berkas fisik untuk verifikasi')->firstOrFail();
        $this->actingAs($this->superAdmin)->post(route('peminjaman.setujui', $suratFisik))->assertSessionHasNoErrors();
        $arsip3->refresh();
        $this->assertSame('Dipinjam', $arsip3->status_ketersediaan);

        // 2. Layanan Informasi dipilih manual tanpa mengunci fisik
        $this->actingAs($this->operator)->post(route('peminjaman.store'), [
            'unit_pengolah_id' => $this->unitArs->id,
            'arsip_ids' => [$arsip1->id],
            'bentuk_layanan' => 'Layanan Informasi',
            'tipe_peminjam' => 'terdaftar',
            'peminjam_id' => $peminjam->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_kembali' => now()->addDays(2)->toDateString(),
            'maksud_keperluan' => 'Penelusuran informasi JIKN mahasiswa',
            'metode_ttd' => 'Manual',
        ])->assertSessionHasNoErrors();

        $suratInfo = SuratPeminjaman::where('maksud_keperluan', 'Penelusuran informasi JIKN mahasiswa')->firstOrFail();

        // 3. Layanan Digital (PDF) tanpa mengunci fisik
        $this->actingAs($this->operator)->post(route('peminjaman.store'), [
            'unit_pengolah_id' => $this->unitArs->id,
            'arsip_ids' => [$arsip2->id],
            'bentuk_layanan' => 'Digital (PDF)',
            'tipe_peminjam' => 'terdaftar',
            'peminjam_id' => $peminjam->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_kembali' => now()->addDays(2)->toDateString(),
            'maksud_keperluan' => 'Permintaan salinan digital PDF',
            'metode_ttd' => 'Manual',
        ])->assertSessionHasNoErrors();

        $suratDigital = SuratPeminjaman::where('maksud_keperluan', 'Permintaan salinan digital PDF')->firstOrFail();

        // Setujui keduanya sebagai Super Admin / Kabid
        $this->actingAs($this->superAdmin)->post(route('peminjaman.setujui', $suratInfo))->assertSessionHasNoErrors();
        $this->actingAs($this->superAdmin)->post(route('peminjaman.setujui', $suratDigital))->assertSessionHasNoErrors();

        $suratInfo->refresh();
        $suratDigital->refresh();
        $arsip1->refresh();
        $arsip2->refresh();

        $this->assertSame('Aktif', $suratInfo->status);
        $this->assertSame('Aktif', $suratDigital->status);
        $this->assertSame('Tersedia', $arsip1->status_ketersediaan);
        $this->assertSame('Tersedia', $arsip2->status_ketersediaan);

        // Render PDF & Unduh binary PDF
        $pengaturan = Pengaturan::pluck('nilai', 'kunci');
        $htmlInfo = view('peminjaman.pdf', ['peminjaman' => $suratInfo->load(['peminjam', 'unitPengolah', 'petugas', 'disetujuiOleh', 'items.arsip.unitPengolah']), 'pengaturan' => $pengaturan])->render();
        $this->assertStringContainsString('SURAT BUKTI LAYANAN INFORMASI ARSIP', $htmlInfo);

        $htmlDigital = view('peminjaman.pdf', ['peminjaman' => $suratDigital->load(['peminjam', 'unitPengolah', 'petugas', 'disetujuiOleh', 'items.arsip.unitPengolah']), 'pengaturan' => $pengaturan])->render();
        $this->assertStringContainsString('SURAT BUKTI LAYANAN ARSIP DIGITAL (PDF)', $htmlDigital);

        $pdfResponse = $this->actingAs($this->operator)->get(route('peminjaman.pdf', $suratDigital));
        $pdfResponse->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        // Halaman pengembalian menampilkan badge Non-Fisik
        $this->actingAs($this->operator)
            ->get(route('pengembalian.index'))
            ->assertOk()
            ->assertSee('Non-Fisik (Tanpa Scan)');

        // Selesaikan layanan non-fisik -> status Selesai, ketersediaan fisik tetap Tersedia
        $itemInfo = $suratInfo->items()->firstOrFail();
        $this->actingAs($this->operator)->post(route('pengembalian.proses', $suratInfo), [
            'item_ids' => [$itemInfo->id],
            'kondisi_kembali' => [$itemInfo->id => 'Baik'],
            'catatan' => [$itemInfo->id => 'Pembacaan di tempat selesai'],
        ])->assertSessionHasNoErrors();

        $suratInfo->refresh();
        $arsip1->refresh();
        $this->assertSame('Selesai', $suratInfo->status);
        $this->assertSame('Tersedia', $arsip1->status_ketersediaan);
    }

    public function test_poin3_bidang_pembuat_surat_operator_dan_kategori_peminjam(): void
    {
        $arsipDinamis = Arsip::where('status_ketersediaan', 'Tersedia')->where('aktif', true)->firstOrFail();

        // Operator (ARS) bisa memilih Bidang Pembuat Surat = Sekretariat (SEK) dengan peminjam kategori SKPD tanpa alamat wajib
        $this->actingAs($this->operator)->post(route('peminjaman.store'), [
            'unit_pengolah_id' => $this->unitSek->id,
            'arsip_ids' => [$arsipDinamis->id],
            'bentuk_layanan' => 'Fisik',
            'tipe_peminjam' => 'baru',
            'nama_peminjam' => 'Rina Kartika, S.Sos',
            'jenis_peminjam' => 'SKPD',
            'instansi_unit' => 'Dinas Kesehatan Kab. Cirebon',
            'no_hp' => '081234567890',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_kembali' => now()->addDays(2)->toDateString(),
            'maksud_keperluan' => 'Verifikasi dokumen kepegawaian SKPD',
            'metode_ttd' => 'Manual',
        ])->assertSessionHasNoErrors();

        $surat = SuratPeminjaman::where('maksud_keperluan', 'Verifikasi dokumen kepegawaian SKPD')->firstOrFail();
        $this->assertSame($this->unitSek->id, $surat->unit_pengolah_id);
        $this->assertStringContainsString('/SPA/SEK/', $surat->nomor_surat);
        $this->assertSame('SKPD', $surat->peminjam->jenis);
        $this->assertTrue($surat->dapatDiaksesOleh($this->operator));

        // Operator tetap bisa membuka detail surat SEK yang dibuatnya
        $this->actingAs($this->operator)
            ->get(route('peminjaman.show', $surat))
            ->assertOk()
            ->assertSee('Sekretariat (SEK)')
            ->assertSee('SKPD');

        $this->actingAs($this->operator)->get(route('peminjaman.index'))->assertOk();
        $this->actingAs($this->operator)->get(route('peminjaman.create'))->assertOk();
        $this->actingAs($this->operator)->get(route('peminjam.index'))->assertOk()->assertSee('SKPD / Dinas Lain');
        $this->actingAs($this->operator)->get(route('peminjam.show', $surat->peminjam))->assertOk()->assertSee('SKPD / Dinas Lain');

        // Kategori Kecamatan / Kelurahan via endpoint Peminjam juga tersimpan tanpa alamat wajib
        $this->actingAs($this->operator)->post(route('peminjam.store'), [
            'jenis' => 'Kecamatan / Kelurahan',
            'nama' => 'Camat Sumber',
            'instansi_unit' => 'Kecamatan Sumber',
            'no_hp' => '085299887766',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('peminjam', [
            'nama' => 'Camat Sumber',
            'jenis' => 'Kecamatan / Kelurahan',
        ]);

        // Kategori Eksternal tanpa alamat wajib harus ditolak validasi
        $this->actingAs($this->operator)->post(route('peminjam.store'), [
            'jenis' => 'Eksternal',
            'nama' => 'Peneliti Luar',
            'instansi_unit' => 'Universitas Cirebon',
            'no_hp' => '089911223344',
        ])->assertSessionHasErrors('alamat');
    }

    public function test_poin5_revisi_detail_arsip_tanpa_mode_penyerahan_p2a_dan_ttd_petugas(): void
    {
        $arsip = Arsip::create([
            'unit_pengolah_id' => $this->unitArs->id,
            'kode_internal' => 'ARS-2026-00-9903',
            'uraian_berkas' => 'Berkas Kontrak Pembangunan',
            'uraian_informasi' => 'Surat perjanjian kerja dan berita acara serah terima tahap 1.',
            'tahun' => 2026,
            'klasifikasi_keamanan' => 'Biasa/Terbuka',
            'status_ketersediaan' => 'Tersedia',
            'aktif' => true,
        ]);
        $peminjam = Peminjam::firstOrFail();

        // 1. Halaman create menampilkan detail arsip modal & pratinjau, serta TIDAK menampilkan opsi mode penyerahan
        $this->actingAs($this->superAdmin)
            ->get(route('peminjaman.create'))
            ->assertOk()
            ->assertSee('modalDetailArsipPinjam')
            ->assertSee('previewArsipPicker')
            ->assertSee('Surat perjanjian kerja dan berita acara serah terima tahap 1.')
            ->assertSee('Bidang P2A')
            ->assertDontSee('name="mode_peminjaman"', false)
            ->assertDontSee('Langsung Diproses')
            ->assertDontSee('Janjian / Dipesan');

        // 2. Pengaturan Penanggung Jawab Bidang menampilkan Bidang P2A
        $this->assertDatabaseHas('unit_pengolah', ['kode' => 'P2A', 'nama' => 'Bidang P2A']);
        $this->actingAs($this->superAdmin)
            ->get(route('pengaturan.index'))
            ->assertOk()
            ->assertSee('Bidang P2A');

        // 3. SuperAdmin (Super Admin SIPA) tidak boleh menerbitkan surat tanpa nama petugas layanan asli
        $this->actingAs($this->superAdmin)->post(route('peminjaman.store'), [
            'unit_pengolah_id' => $this->unitArs->id,
            'arsip_ids' => [$arsip->id],
            'bentuk_layanan' => 'Fisik',
            'tipe_peminjam' => 'terdaftar',
            'peminjam_id' => $peminjam->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_kembali' => now()->addDays(3)->toDateString(),
            'maksud_keperluan' => 'Uji tolak nama Super Admin SIPA',
            'metode_ttd' => 'Manual',
        ])->assertSessionHasErrors('petugas_layanan_nama');

        // 4. Mengisi nama, NIP, dan bidang petugas layanan asli -> tersimpan & tercetak di PDF (bukan Super Admin SIPA)
        $this->actingAs($this->superAdmin)->post(route('peminjaman.store'), [
            'unit_pengolah_id' => $this->unitArs->id,
            'arsip_ids' => [$arsip->id],
            'bentuk_layanan' => 'Fisik',
            'tipe_peminjam' => 'terdaftar',
            'peminjam_id' => $peminjam->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_kembali' => now()->addDays(3)->toDateString(),
            'maksud_keperluan' => 'Audit fisik dokumen kontrak',
            'metode_ttd' => 'Manual',
            'petugas_layanan_nama' => 'Budi Santoso, S.IP',
            'petugas_layanan_nip' => '199004152015031002',
            'petugas_layanan_bidang' => 'Bidang P2A',
        ])->assertSessionHasNoErrors();

        $surat = SuratPeminjaman::where('maksud_keperluan', 'Audit fisik dokumen kontrak')->firstOrFail();
        $this->assertFalse((bool) $surat->is_janjian);
        $this->assertSame('Budi Santoso, S.IP', $surat->petugas_layanan_nama);
        $this->assertSame('199004152015031002', $surat->petugas_layanan_nip);
        $this->assertSame('Bidang P2A', $surat->petugas_layanan_bidang);

        $this->actingAs($this->superAdmin)
            ->get(route('peminjaman.show', $surat))
            ->assertOk()
            ->assertSee('Budi Santoso, S.IP')
            ->assertSee('199004152015031002')
            ->assertSee('Bidang P2A');

        $pengaturan = Pengaturan::pluck('nilai', 'kunci');
        $htmlPdf = view('peminjaman.pdf', [
            'peminjaman' => $surat->load(['peminjam', 'unitPengolah', 'petugas.unitPengolah', 'disetujuiOleh', 'items.arsip.unitPengolah']),
            'pengaturan' => $pengaturan,
        ])->render();

        $this->assertStringContainsString('Budi Santoso, S.IP', $htmlPdf);
        $this->assertStringContainsString('NIP. 199004152015031002', $htmlPdf);
        $this->assertStringContainsString('Bidang P2A', $htmlPdf);
        $this->assertStringNotContainsString('Super Admin SIPA', $htmlPdf);
    }

    public function test_poin6_peran_label_dan_ekspor_excel_laporan(): void
    {
        $this->assertSame('Super Admin / Kabid', $this->superAdmin->peranLabel());
        $this->assertSame('Operator Bidang', $this->operator->peranLabel());
        $this->assertEqualsCanonicalizing(['SuperAdmin', 'Operator'], User::distinct()->pluck('peran')->toArray());

        $this->actingAs($this->superAdmin)->get(route('dasbor'))->assertOk()->assertSee('Super Admin / Kabid');
        $this->actingAs($this->superAdmin)->get(route('pengguna.index'))->assertOk()->assertSee('Super Admin / Kabid');
        $this->actingAs($this->superAdmin)->get(route('laporan.index'))->assertOk();

        // Uji struktur Excel hasil LaporanController::buildRekapSpreadsheet()
        $peminjam = Peminjam::create([
            'nama' => 'Siti Aminah',
            'jenis' => 'SKPD',
            'instansi_unit' => 'Dinas Pendidikan Kab. Cirebon',
            'no_hp' => '081200009999',
        ]);
        $arsip = Arsip::firstOrFail();

        $surat = SuratPeminjaman::create([
            'nomor_surat' => '999/SPA/SEK/XI/2026',
            'tanggal_surat' => now()->toDateString(),
            'unit_pengolah_id' => $this->unitSek->id,
            'peminjam_id' => $peminjam->id,
            'bentuk_layanan' => 'Digital (PDF)',
            'is_janjian' => true,
            'rencana_ambil_pada' => now()->addDay(),
            'maksud_keperluan' => 'Verifikasi uji Excel',
            'jaminan' => 'Surat Tugas',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_kembali' => now()->addDays(2)->toDateString(),
            'lama_pinjam_hari' => 3,
            'butuh_persetujuan' => true,
            'status_persetujuan' => 'Disetujui',
            'metode_ttd' => 'Manual',
            'petugas_id' => $this->operator->id,
            'status' => 'Janjian',
        ]);
        $surat->items()->create(['arsip_id' => $arsip->id, 'kondisi_keluar' => 'Baik']);
        $surat->load(['peminjam', 'unitPengolah', 'items.arsip']);

        $controller = new class extends LaporanController {
            public function buildForTest($data, $tahun): Spreadsheet
            {
                return $this->buildRekapSpreadsheet($data, $tahun);
            }
        };

        $spreadsheet = $controller->buildForTest(collect([$surat]), (int) date('Y'));
        $tmpPath = tempnam(sys_get_temp_dir(), 'sipa_exp_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        try {
            $loaded = IOFactory::load($tmpPath)->getActiveSheet();
            $this->assertSame('Bidang Pembuat Surat', $loaded->getCell('C4')->getValue());
            $this->assertSame('Bentuk Layanan', $loaded->getCell('D4')->getValue());
            $this->assertSame('Kategori Peminjam', $loaded->getCell('I4')->getValue());
            $this->assertSame('Nomor Telepon', $loaded->getCell('K4')->getValue());
            $this->assertSame('Status', $loaded->getCell('P4')->getValue());

            $this->assertSame($this->unitSek->nama, $loaded->getCell('C5')->getValue());
            $this->assertSame('Digital (PDF)', $loaded->getCell('D5')->getValue());
            $this->assertSame('SKPD / Dinas Lain', $loaded->getCell('I5')->getValue());
            $this->assertSame('081200009999', $loaded->getCell('K5')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $loaded->getCell('K5')->getDataType());
            $this->assertSame('Janjian', $loaded->getCell('P5')->getValue());
        } finally {
            @unlink($tmpPath);
        }
    }

    public function test_poin7_ekspor_pdf_laporan_dan_peminjam(): void
    {
        $peminjam = Peminjam::create([
            'nama' => 'Dedi Kurniawan',
            'jenis' => 'Internal',
            'nip_nik' => '199105052019031002',
            'jabatan' => 'Analis Arsip',
            'instansi_unit' => 'Bidang Pengelolaan Arsip',
            'no_hp' => '081277778888',
            'jaminan' => 'Kartu Pegawai / ID Card',
        ]);

        // 1. Halaman Laporan Tahunan & Ekspor PDF
        $laporanIndex = $this->actingAs($this->superAdmin)->get(route('laporan.index'));
        $laporanIndex->assertOk()
            ->assertSee('Ekspor ke PDF (.pdf)')
            ->assertSee('Tren Peminjaman Bulanan');

        $laporanPdf = $this->actingAs($this->superAdmin)->get(route('laporan.pdf', ['tahun' => date('Y')]));
        $laporanPdf->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        // 2. Halaman Data Peminjam, Ekspor Direktori PDF, & Ekspor Riwayat Peminjam PDF
        $peminjamIndex = $this->actingAs($this->superAdmin)->get(route('peminjam.index'));
        $peminjamIndex->assertOk()
            ->assertSee('Ekspor PDF')
            ->assertSee('Dedi Kurniawan');

        $peminjamPdf = $this->actingAs($this->superAdmin)->get(route('peminjam.pdf', ['jenis' => 'Internal']));
        $peminjamPdf->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $peminjamShow = $this->actingAs($this->superAdmin)->get(route('peminjam.show', $peminjam));
        $peminjamShow->assertOk()
            ->assertSee('Ekspor Riwayat PDF');

        $peminjamShowPdf = $this->actingAs($this->superAdmin)->get(route('peminjam.show.pdf', $peminjam));
        $peminjamShowPdf->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_poin8_mode_gelap_terang_dan_panduan_alur_aplikasi(): void
    {
        // 1. Halaman Panduan & Alur Kerja SIPA dapat diakses oleh Super Admin dan Operator
        $panduanAdmin = $this->actingAs($this->superAdmin)->get(route('panduan'));
        $panduanAdmin->assertOk()
            ->assertSee('Panduan &amp; Tata Cara Penggunaan SIPA', false)
            ->assertSee('Peta Alur Kerja Sirkulasi Arsip')
            ->assertSee('Data &amp; Impor Arsip', false)
            ->assertSee('Direktori Peminjam')
            ->assertSee('Buat Surat Peminjaman')
            ->assertSee('Pemindai OpenCV')
            ->assertSee('Pengembalian')
            ->assertSee('Laporan &amp; Ekspor', false)
            ->assertSee('Peminjaman Fisik')
            ->assertSee('Salinan Digital (PDF)')
            ->assertSee('Layanan Informasi')
            ->assertSee('Tanya Jawab &amp; Penanganan Kendala (FAQ)', false);

        $panduanOperator = $this->actingAs($this->operator)->get(route('panduan'));
        $panduanOperator->assertOk()
            ->assertSee('Panduan &amp; Tata Cara Penggunaan SIPA', false);

        // 2. Dasbor memuat elemen Panduan, modal panduan cepat, dan switch tema gelap/terang
        $dasbor = $this->actingAs($this->superAdmin)->get(route('dasbor'));
        $dasbor->assertOk()
            ->assertSee('themeToggleBtn')
            ->assertSee('toggleTheme()', false)
            ->assertSee('modalPanduanCepat')
            ->assertSee('Buku Panduan &amp; Alur', false);
    }
}
