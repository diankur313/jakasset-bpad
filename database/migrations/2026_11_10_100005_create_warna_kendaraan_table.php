<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warna_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->string('kode_hex', 7)->nullable();
            $table->timestamps();
        });

        Schema::table('kendaraan_dinas', function (Blueprint $table) {
            $table->foreignId('warna_kendaraan_id')->nullable()->after('master_kendaraan_id')
                ->constrained('warna_kendaraan')->restrictOnDelete();
        });

        // Pindahkan isian teks bebas lama ke master warna (dirapikan: Title Case, spasi tunggal).
        $now = now();
        DB::table('kendaraan_dinas')->select('id', 'warna')->orderBy('id')->each(function (object $row) use ($now): void {
            $nama = Str::title(preg_replace('/\s+/', ' ', trim((string) $row->warna)));

            if ($nama === '') {
                return;
            }

            DB::table('warna_kendaraan')->insertOrIgnore(['nama' => $nama, 'created_at' => $now, 'updated_at' => $now]);

            DB::table('kendaraan_dinas')->where('id', $row->id)->update([
                'warna_kendaraan_id' => DB::table('warna_kendaraan')->where('nama', $nama)->value('id'),
            ]);
        });

        Schema::table('kendaraan_dinas', function (Blueprint $table) {
            $table->dropColumn('warna');
        });
    }

    public function down(): void
    {
        Schema::table('kendaraan_dinas', function (Blueprint $table) {
            $table->string('warna', 50)->nullable()->after('master_kendaraan_id');
        });

        DB::table('kendaraan_dinas')->whereNotNull('warna_kendaraan_id')->orderBy('id')->each(function (object $row): void {
            DB::table('kendaraan_dinas')->where('id', $row->id)->update([
                'warna' => DB::table('warna_kendaraan')->where('id', $row->warna_kendaraan_id)->value('nama'),
            ]);
        });

        Schema::table('kendaraan_dinas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warna_kendaraan_id');
        });

        Schema::dropIfExists('warna_kendaraan');
    }
};
