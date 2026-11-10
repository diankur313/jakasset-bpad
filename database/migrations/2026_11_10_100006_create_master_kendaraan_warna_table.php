<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Warna yang tersedia untuk tiap master kendaraan (diatur di form Master Data > Kendaraan).
     */
    public function up(): void
    {
        Schema::create('master_kendaraan_warna', function (Blueprint $table) {
            $table->foreignId('master_kendaraan_id')->constrained('master_kendaraan')->cascadeOnDelete();
            $table->foreignId('warna_kendaraan_id')->constrained('warna_kendaraan')->cascadeOnDelete();

            $table->primary(['master_kendaraan_id', 'warna_kendaraan_id']);
        });

        // Kombinasi master + warna yang sudah dipakai kendaraan dinas otomatis menjadi warna tersedia.
        $pairs = DB::table('kendaraan_dinas')
            ->whereNotNull('warna_kendaraan_id')
            ->select('master_kendaraan_id', 'warna_kendaraan_id')
            ->distinct()
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        DB::table('master_kendaraan_warna')->insertOrIgnore($pairs);
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kendaraan_warna');
    }
};
