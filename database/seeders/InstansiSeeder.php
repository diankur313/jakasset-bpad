<?php

namespace Database\Seeders;

use App\Models\Instansi;
use Illuminate\Database\Seeder;

class InstansiSeeder extends Seeder
{
    /**
     * Contoh awal instansi pemilik kendaraan (perangkat daerah).
     */
    public function run(): void
    {
        $data = [
            ['nama' => 'Dinas Pendidikan', 'singkatan' => 'Disdik'],
            ['nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes'],
            ['nama' => 'Dinas Pekerjaan Umum dan Penataan Ruang', 'singkatan' => 'PUPR'],
        ];

        foreach ($data as $row) {
            Instansi::firstOrCreate(['nama' => $row['nama']], $row);
        }
    }
}
