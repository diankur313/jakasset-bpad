<?php

namespace Database\Seeders;

use App\Models\WarnaKendaraan;
use Illuminate\Database\Seeder;

class WarnaKendaraanSeeder extends Seeder
{
    /**
     * Warna standar kendaraan dinas.
     */
    public function run(): void
    {
        $data = [
            'Hitam' => '#000000',
            'Putih' => '#FFFFFF',
            'Silver' => '#C0C0C0',
            'Abu-Abu' => '#808080',
            'Merah' => '#C62828',
            'Merah Marun' => '#800000',
            'Biru' => '#1565C0',
            'Biru Dongker' => '#1A237E',
            'Hijau' => '#2E7D32',
            'Kuning' => '#F9A825',
            'Oranye' => '#EF6C00',
            'Coklat' => '#6D4C41',
        ];

        foreach ($data as $nama => $hex) {
            WarnaKendaraan::firstOrCreate(['nama' => $nama], ['kode_hex' => $hex]);
        }
    }
}
