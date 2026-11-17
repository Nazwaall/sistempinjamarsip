<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            if (Schema::hasIndex('arsip', 'idx_arsip_kategori_naskah')) {
                $table->dropIndex('idx_arsip_kategori_naskah');
            }

            $toDrop = array_values(array_filter(
                ['kategori_arsip', 'jenis_naskah', 'sumber_penciptaan'],
                fn ($col) => Schema::hasColumn('arsip', $col)
            ));

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });

        DB::table('surat_peminjaman')
            ->where('bentuk_layanan', 'Layanan Informasi (Statis)')
            ->update(['bentuk_layanan' => 'Layanan Informasi']);
    }

    public function down(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            if (!Schema::hasColumn('arsip', 'kategori_arsip')) {
                $table->string('kategori_arsip', 30)->default('Dinamis Inaktif')->after('unit_pengolah_asal');
            }
            if (!Schema::hasColumn('arsip', 'jenis_naskah')) {
                $table->string('jenis_naskah', 60)->default('Surat Biasa')->after('kode_klasifikasi');
            }
            if (!Schema::hasColumn('arsip', 'sumber_penciptaan')) {
                $table->string('sumber_penciptaan', 30)->default('Konvensional')->after('tingkat_perkembangan');
            }
            if (!Schema::hasIndex('arsip', 'idx_arsip_kategori_naskah')) {
                $table->index(['kategori_arsip', 'jenis_naskah'], 'idx_arsip_kategori_naskah');
            }
        });

        DB::table('surat_peminjaman')
            ->where('bentuk_layanan', 'Layanan Informasi')
            ->update(['bentuk_layanan' => 'Layanan Informasi (Statis)']);
    }
};
