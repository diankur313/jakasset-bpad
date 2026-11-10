<?php

use App\Models\KendaraanDinas;
use App\Support\NomorAsset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penghitung running number per jenis asset per tanggal.
        Schema::create('nomor_asset_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_asset', 30);
            $table->date('tanggal');
            $table->unsignedInteger('nomor_terakhir')->default(0);
            $table->timestamps();

            $table->unique(['jenis_asset', 'tanggal']);
        });

        Schema::table('kendaraan_dinas', function (Blueprint $table) {
            $table->string('nomor_asset', 40)->nullable()->unique()->after('id');
        });

        // Isi nomor asset untuk data yang sudah ada, berdasarkan tanggal dibuat.
        DB::table('kendaraan_dinas')->whereNull('nomor_asset')->orderBy('created_at')->orderBy('id')
            ->each(function (object $row): void {
                DB::table('kendaraan_dinas')->where('id', $row->id)->update([
                    'nomor_asset' => NomorAsset::generate(
                        KendaraanDinas::JENIS_ASSET,
                        $row->created_at ? Carbon::parse($row->created_at) : now(),
                    ),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('kendaraan_dinas', function (Blueprint $table) {
            $table->dropUnique(['nomor_asset']);
            $table->dropColumn('nomor_asset');
        });

        Schema::dropIfExists('nomor_asset_sequences');
    }
};
