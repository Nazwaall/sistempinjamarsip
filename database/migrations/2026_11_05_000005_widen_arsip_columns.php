<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE arsip ALTER COLUMN tingkat_perkembangan TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN uraian_berkas TYPE TEXT');
            DB::statement('ALTER TABLE arsip ALTER COLUMN teks_label TYPE TEXT');
            DB::statement('ALTER TABLE arsip ALTER COLUMN kode_klasifikasi TYPE VARCHAR(100)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN lokasi_simpan TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN jra_keterangan TYPE VARCHAR(100)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN klasifikasi_keamanan TYPE VARCHAR(100)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN kode_internal TYPE VARCHAR(60)');
        } else {
            DB::statement("ALTER TABLE arsip
                MODIFY COLUMN tingkat_perkembangan VARCHAR(255) NULL,
                MODIFY COLUMN uraian_berkas TEXT NOT NULL,
                MODIFY COLUMN teks_label TEXT NULL,
                MODIFY COLUMN kode_klasifikasi VARCHAR(100) NULL,
                MODIFY COLUMN lokasi_simpan VARCHAR(255) NULL,
                MODIFY COLUMN jra_keterangan VARCHAR(100) NULL,
                MODIFY COLUMN klasifikasi_keamanan VARCHAR(100) NOT NULL DEFAULT 'Biasa/Terbuka',
                MODIFY COLUMN kode_internal VARCHAR(60) NOT NULL
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE arsip ALTER COLUMN tingkat_perkembangan TYPE VARCHAR(30)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN uraian_berkas TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN teks_label TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN kode_klasifikasi TYPE VARCHAR(50)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN lokasi_simpan TYPE VARCHAR(100)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN jra_keterangan TYPE VARCHAR(30)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN klasifikasi_keamanan TYPE VARCHAR(30)');
            DB::statement('ALTER TABLE arsip ALTER COLUMN kode_internal TYPE VARCHAR(30)');
        } else {
            DB::statement("ALTER TABLE arsip
                MODIFY COLUMN tingkat_perkembangan VARCHAR(30) NULL,
                MODIFY COLUMN uraian_berkas VARCHAR(255) NOT NULL,
                MODIFY COLUMN teks_label VARCHAR(255) NULL,
                MODIFY COLUMN kode_klasifikasi VARCHAR(50) NULL,
                MODIFY COLUMN lokasi_simpan VARCHAR(100) NULL,
                MODIFY COLUMN jra_keterangan VARCHAR(30) NULL,
                MODIFY COLUMN klasifikasi_keamanan VARCHAR(30) NOT NULL DEFAULT 'Biasa/Terbuka',
                MODIFY COLUMN kode_internal VARCHAR(30) NOT NULL
            ");
        }
    }
};
