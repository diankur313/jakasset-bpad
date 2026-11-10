<?php

namespace Database\Seeders;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Models\MasterKendaraan;
use App\Models\WarnaKendaraan;
use Illuminate\Database\Seeder;

class MasterKendaraanSeeder extends Seeder
{
    /**
     * 10 data dummy master kendaraan.
     * Beberapa merek punya lebih dari satu tipe, dan satu tipe punya dua tahun,
     * supaya dropdown berjenjang Merek -> Tipe -> Tahun di Kendaraan Dinas bisa dicoba.
     * Tiap master juga diberi Warna Tersedia (butuh WarnaKendaraanSeeder dijalankan lebih dulu).
     */
    public function run(): void
    {
        $baik = KondisiKendaraan::Baik;
        $ringan = KondisiKendaraan::RusakRingan;
        $berat = KondisiKendaraan::RusakBerat;
        $active = StatusKendaraan::Active;
        $inactive = StatusKendaraan::Inactive;
        $standby = StatusKendaraan::Standby;

        $data = [
            ['Toyota', 'Innova Zenix 2.0 G', 2023, $baik, $active, ['Hitam', 'Putih', 'Silver']],
            ['Toyota', 'Avanza 1.5 G', 2019, $ringan, $standby, ['Hitam', 'Silver']],
            ['Toyota', 'Avanza 1.5 G', 2022, $baik, $active, ['Hitam', 'Putih', 'Abu-Abu']],
            ['Toyota', 'Hilux Double Cabin 2.4 E', 2021, $baik, $active, ['Putih', 'Silver', 'Hitam']],
            ['Mitsubishi', 'Pajero Sport Dakar 2.4', 2022, $baik, $active, ['Hitam', 'Putih']],
            ['Mitsubishi', 'Xpander Ultimate', 2023, $baik, $standby, ['Putih', 'Hitam', 'Abu-Abu']],
            ['Suzuki', 'Ertiga GX', 2018, $berat, $inactive, ['Silver', 'Merah Marun']],
            ['Daihatsu', 'Gran Max Pick Up 1.5', 2020, $ringan, $active, ['Putih', 'Silver']],
            ['Isuzu', 'Elf NLR 55 B (Ambulans)', 2021, $baik, $active, ['Putih']],
            ['Honda', 'Vario 125', 2022, $baik, $active, ['Hitam', 'Merah', 'Biru']],
        ];

        foreach ($data as [$merek, $tipe, $tahun, $kondisi, $status, $warna]) {
            $master = MasterKendaraan::updateOrCreate(
                ['merek' => $merek, 'tipe_model' => $tipe, 'tahun_produksi' => $tahun],
                ['kondisi' => $kondisi, 'status' => $status],
            );

            // Tambah warna tersedia tanpa menghapus warna yang sudah diatur pengguna.
            $master->warna()->syncWithoutDetaching(
                WarnaKendaraan::whereIn('nama', $warna)->pluck('id'),
            );
        }
    }
}
