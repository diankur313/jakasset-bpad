<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('merek');
            $table->string('tipe_model');
            $table->unsignedSmallInteger('tahun_produksi');
            $table->string('kondisi', 20);
            $table->string('status', 20);
            $table->timestamps();

            $table->unique(['merek', 'tipe_model', 'tahun_produksi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kendaraan');
    }
};
