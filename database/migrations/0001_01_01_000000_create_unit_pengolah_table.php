<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_pengolah', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique(); // e.g. ARS, SEK, BUK
            $table->string('nama', 150);
            $table->string('kepala_nama', 150)->nullable();
            $table->string('kepala_nip', 30)->nullable();
            $table->smallInteger('lama_pinjam_standar')->default(7);
            $table->smallInteger('lama_pinjam_maks')->default(7);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_pengolah');
    }
};
