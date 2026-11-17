<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->string('kategori_arsip', 30)->default('Dinamis Inaktif')->after('unit_pengolah_asal');
            $table->string('jenis_naskah', 60)->default('Surat Biasa')->after('kode_klasifikasi');
            $table->string('sumber_penciptaan', 30)->default('Konvensional')->after('tingkat_perkembangan');
            $table->index(['kategori_arsip', 'jenis_naskah'], 'idx_arsip_kategori_naskah');
        });

        Schema::table('peminjam', function (Blueprint $table) {
            $table->string('jenis', 40)->default('Internal')->change();
        });

        Schema::table('surat_peminjaman', function (Blueprint $table) {
            $table->string('bentuk_layanan', 40)->default('Fisik')->after('peminjam_id');
            $table->boolean('is_janjian')->default(false)->after('status');
            $table->dateTime('rencana_ambil_pada')->nullable()->after('is_janjian');
            $table->string('catatan_janjian', 255)->nullable()->after('rencana_ambil_pada');
            $table->index(['bentuk_layanan', 'is_janjian'], 'idx_surat_layanan_janjian');
        });
    }

    public function down(): void
    {
        Schema::table('surat_peminjaman', function (Blueprint $table) {
            $table->dropIndex('idx_surat_layanan_janjian');
            $table->dropColumn(['bentuk_layanan', 'is_janjian', 'rencana_ambil_pada', 'catatan_janjian']);
        });

        Schema::table('peminjam', function (Blueprint $table) {
            $table->string('jenis', 20)->default('Internal')->change();
        });

        Schema::table('arsip', function (Blueprint $table) {
            $table->dropIndex('idx_arsip_kategori_naskah');
            $table->dropColumn(['kategori_arsip', 'jenis_naskah', 'sumber_penciptaan']);
        });
    }
};
