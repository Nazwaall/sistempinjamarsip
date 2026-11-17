<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_peminjaman', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_peminjaman', 'petugas_layanan_nama')) {
                $table->string('petugas_layanan_nama', 150)->nullable()->after('petugas_id');
            }
            if (!Schema::hasColumn('surat_peminjaman', 'petugas_layanan_nip')) {
                $table->string('petugas_layanan_nip', 30)->nullable()->after('petugas_layanan_nama');
            }
            if (!Schema::hasColumn('surat_peminjaman', 'petugas_layanan_bidang')) {
                $table->string('petugas_layanan_bidang', 150)->nullable()->after('petugas_layanan_nip');
            }
        });

        if (!DB::table('unit_pengolah')->where('kode', 'P2A')->exists()) {
            DB::table('unit_pengolah')->insert([
                'kode' => 'P2A',
                'nama' => 'Bidang P2A',
                'kepala_nama' => 'Kepala Bidang P2A',
                'kepala_nip' => null,
                'lama_pinjam_standar' => 7,
                'lama_pinjam_maks' => 7,
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('surat_peminjaman', function (Blueprint $table) {
            $toDrop = array_values(array_filter(
                ['petugas_layanan_nama', 'petugas_layanan_nip', 'petugas_layanan_bidang'],
                fn ($col) => Schema::hasColumn('surat_peminjaman', $col)
            ));

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
