<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_peminjaman_item', function (Blueprint $table) {
            $table->unsignedTinyInteger('skor_integritas')->nullable()->after('kondisi_kembali');
            $table->string('ringkasan_kerusakan', 255)->nullable()->after('skor_integritas');
            $table->json('detail_kerusakan_json')->nullable()->after('ringkasan_kerusakan');
            $table->string('foto_kembali_path')->nullable()->after('detail_kerusakan_json');
        });

        Schema::table('arsip', function (Blueprint $table) {
            $table->unsignedTinyInteger('skor_integritas_terakhir')->nullable()->after('status_ketersediaan');
            $table->string('catatan_kondisi_terakhir', 255)->nullable()->after('skor_integritas_terakhir');
            $table->timestamp('terakhir_diperiksa_pada')->nullable()->after('catatan_kondisi_terakhir');
        });
    }

    public function down(): void
    {
        Schema::table('surat_peminjaman_item', function (Blueprint $table) {
            $table->dropColumn(['skor_integritas', 'ringkasan_kerusakan', 'detail_kerusakan_json', 'foto_kembali_path']);
        });

        Schema::table('arsip', function (Blueprint $table) {
            $table->dropColumn(['skor_integritas_terakhir', 'catatan_kondisi_terakhir', 'terakhir_diperiksa_pada']);
        });
    }
};
