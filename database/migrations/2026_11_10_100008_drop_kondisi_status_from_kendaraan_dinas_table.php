<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kondisi & Status kendaraan dinas selalu mengikuti Master Kendaraan, jadi tidak disimpan ulang
     * di tabel kendaraan_dinas (mencegah data ganda / tidak sinkron).
     */
    public function up(): void
    {
        Schema::table('kendaraan_dinas', function (Blueprint $table) {
            $table->dropColumn(['kondisi', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('kendaraan_dinas', function (Blueprint $table) {
            $table->string('kondisi', 20)->nullable()->after('warna_kendaraan_id');
            $table->string('status', 20)->nullable()->after('kondisi');
        });

        DB::table('kendaraan_dinas')->orderBy('id')->each(function (object $row): void {
            $master = DB::table('master_kendaraan')->where('id', $row->master_kendaraan_id)->first();

            DB::table('kendaraan_dinas')->where('id', $row->id)->update([
                'kondisi' => $master?->kondisi,
                'status' => $master?->status,
            ]);
        });
    }
};
