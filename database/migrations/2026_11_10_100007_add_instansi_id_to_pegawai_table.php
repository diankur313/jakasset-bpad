<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pegawai terdaftar di satu instansi (perangkat daerah).
     * Kolom nullable di database agar data lama tetap valid; wajib diisi lewat form.
     */
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->foreignId('instansi_id')->nullable()->after('golongan')
                ->constrained('instansi')->restrictOnDelete();
        });

        // Pegawai yang sudah memegang kendaraan dinas ikut ke instansi pemilik kendaraan tersebut.
        DB::table('kendaraan_dinas')->whereNotNull('pegawai_id')
            ->select('pegawai_id', 'instansi_id')->orderBy('id')
            ->each(function (object $row): void {
                DB::table('pegawai')->where('id', $row->pegawai_id)->whereNull('instansi_id')
                    ->update(['instansi_id' => $row->instansi_id]);
            });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instansi_id');
        });
    }
};
