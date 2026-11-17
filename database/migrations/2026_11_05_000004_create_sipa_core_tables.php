<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Peminjam
        Schema::create('peminjam', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 20)->default('Internal'); // Internal, Eksternal
            $table->string('nama', 150);
            $table->string('nip_nik', 30)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('instansi_unit', 150);
            $table->string('alamat', 255)->nullable();
            $table->string('no_hp', 25);
            $table->string('email', 100)->nullable();
            $table->timestamps();
        });

        // 2. Arsip
        Schema::create('arsip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_pengolah_id')->constrained('unit_pengolah')->cascadeOnDelete();
            $table->string('unit_pengolah_asal', 150)->nullable();
            $table->string('kode_internal', 30)->unique();
            $table->string('kode_klasifikasi', 50)->nullable();
            $table->string('uraian_berkas', 255);
            $table->text('uraian_informasi')->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->smallInteger('tahun');
            $table->smallInteger('bulan')->nullable();
            $table->string('tingkat_perkembangan', 30)->nullable();
            $table->smallInteger('jra_aktif')->nullable();
            $table->smallInteger('jra_inaktif')->nullable();
            $table->string('jra_keterangan', 30)->nullable();
            $table->string('lokasi_simpan', 100)->nullable();
            $table->string('teks_label', 255)->nullable();
            $table->string('klasifikasi_keamanan', 30)->default('Biasa/Terbuka');
            $table->boolean('dikecualikan')->default(false);
            $table->string('status_ketersediaan', 30)->default('Tersedia'); // Tersedia, Dipinjam, Hilang, Rusak
            $table->string('kode_qr', 50)->nullable()->unique();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['unit_pengolah_id', 'tahun', 'bulan']);
            $table->index('kode_klasifikasi');
            $table->index('status_ketersediaan');
        });

        // 3. Surat Peminjaman
        Schema::create('surat_peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 60)->unique();
            $table->date('tanggal_surat');
            $table->foreignId('unit_pengolah_id')->constrained('unit_pengolah')->cascadeOnDelete();
            $table->foreignId('peminjam_id')->constrained('peminjam')->cascadeOnDelete();
            $table->text('maksud_keperluan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_kembali');
            $table->smallInteger('lama_pinjam_hari');
            $table->smallInteger('diperpanjang_kali')->default(0);
            $table->boolean('butuh_persetujuan')->default(true);
            $table->string('status_persetujuan', 30)->default('Menunggu'); // Menunggu, Disetujui, Ditolak
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_disetujui')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->string('metode_ttd', 20)->nullable(); // Digital, Manual
            $table->string('ttd_peminjam_path')->nullable();
            $table->string('surat_ttd_path')->nullable();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('Draf'); // Draf, Menunggu Persetujuan, Aktif, Selesai, Terlambat, Ditolak, Dibatalkan
            $table->timestamp('tanggal_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['unit_pengolah_id', 'status']);
            $table->index('tanggal_kembali');
        });

        // 4. Surat Peminjaman Item
        Schema::create('surat_peminjaman_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat_peminjaman')->cascadeOnDelete();
            $table->foreignId('arsip_id')->constrained('arsip')->cascadeOnDelete();
            $table->string('kondisi_keluar')->default('Baik');
            $table->timestamp('tanggal_kembali_riil')->nullable();
            $table->string('kondisi_kembali')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();
        });

        // 5. Bukti Foto
        Schema::create('bukti_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat_peminjaman')->cascadeOnDelete();
            $table->string('jenis', 50); // Serah Terima, Pengembalian, Identitas, Surat Bertanda Tangan
            $table->string('sumber', 20)->default('Webcam'); // Webcam, Unggah
            $table->string('file_path');
            $table->string('nama_asli')->nullable();
            $table->text('ocr_text')->nullable();
            $table->foreignId('diunggah_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 6. Perpanjangan
        Schema::create('perpanjangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat_peminjaman')->cascadeOnDelete();
            $table->date('tanggal_kembali_lama');
            $table->date('tanggal_kembali_baru');
            $table->text('alasan');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 7. Log Audit
        Schema::create('log_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 100);
            $table->string('entitas', 100);
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->json('detail')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 8. Pengaturan
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_pengolah_id')->nullable()->constrained('unit_pengolah')->cascadeOnDelete();
            $table->string('kunci', 100)->unique();
            $table->text('nilai')->nullable();
            $table->timestamps();
        });

        // 9. Impor Arsip
        Schema::create('impor_arsip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_pengolah_id')->constrained('unit_pengolah')->cascadeOnDelete();
            $table->string('nama_file');
            $table->integer('jumlah_baris')->default(0);
            $table->integer('jumlah_berhasil')->default(0);
            $table->integer('jumlah_gagal')->default(0);
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 10. Nomor Surat Counter
        Schema::create('nomor_surat_counter', function (Blueprint $table) {
            $table->foreignId('unit_pengolah_id')->constrained('unit_pengolah')->cascadeOnDelete();
            $table->smallInteger('tahun');
            $table->integer('terakhir')->default(0);
            $table->primary(['unit_pengolah_id', 'tahun']);
        });

        // Ekstensi PostgreSQL dan Indeks Khusus
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS unq_arsip_aktif_pinjam ON surat_peminjaman_item (arsip_id) WHERE tanggal_kembali_riil IS NULL;');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_arsip_trgm_uraian ON arsip USING gin (uraian_berkas gin_trgm_ops);');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_arsip_trgm_label ON arsip USING gin (teks_label gin_trgm_ops);');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_counter');
        Schema::dropIfExists('impor_arsip');
        Schema::dropIfExists('pengaturan');
        Schema::dropIfExists('log_audit');
        Schema::dropIfExists('perpanjangan');
        Schema::dropIfExists('bukti_foto');
        Schema::dropIfExists('surat_peminjaman_item');
        Schema::dropIfExists('surat_peminjaman');
        Schema::dropIfExists('arsip');
        Schema::dropIfExists('peminjam');
    }
};
