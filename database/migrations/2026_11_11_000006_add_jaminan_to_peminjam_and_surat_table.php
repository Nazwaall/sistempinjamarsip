<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjam', function (Blueprint $table) {
            $table->string('jaminan', 100)->nullable()->after('email');
        });

        Schema::table('surat_peminjaman', function (Blueprint $table) {
            $table->string('jaminan', 100)->nullable()->after('maksud_keperluan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_peminjaman', function (Blueprint $table) {
            $table->dropColumn('jaminan');
        });

        Schema::table('peminjam', function (Blueprint $table) {
            $table->dropColumn('jaminan');
        });
    }
};
