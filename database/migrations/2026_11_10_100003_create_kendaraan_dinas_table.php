<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kendaraan_dinas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_polisi', 15)->unique();
            // restrictOnDelete: data master yang masih dipakai tidak bisa terhapus di level database.
            $table->foreignId('master_kendaraan_id')->constrained('master_kendaraan')->restrictOnDelete();
            $table->string('warna', 50);
            $table->string('kondisi', 20);
            $table->string('status', 20);
            $table->foreignId('instansi_id')->constrained('instansi')->restrictOnDelete();
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawai')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kendaraan_dinas');
    }
};
