<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * 10 data dummy pegawai.
     * Format NIP: tanggal lahir (8) + TMT CPNS tahun-bulan (6) + jenis kelamin (1) + nomor urut (3).
     */
    public function run(): void
    {
        $data = [
            ['nama' => 'Ahmad Fauzi, S.T., M.T.', 'nip' => '197503122000031001', 'jabatan' => 'Kepala Bidang Pengelolaan Aset', 'golongan' => 'IV/b'],
            ['nama' => 'Sri Wahyuni, S.E., M.M.', 'nip' => '197808242003122002', 'jabatan' => 'Kepala Sub Bidang Inventarisasi Aset', 'golongan' => 'IV/a'],
            ['nama' => 'Budi Santoso, S.H.', 'nip' => '198001152005011003', 'jabatan' => 'Kepala Seksi Pemanfaatan Aset', 'golongan' => 'III/d'],
            ['nama' => 'Dewi Lestari, S.Kom.', 'nip' => '198504302009032004', 'jabatan' => 'Analis Barang Milik Daerah', 'golongan' => 'III/c'],
            ['nama' => 'Rudi Hartono, S.Sos.', 'nip' => '198207182008011005', 'jabatan' => 'Pengelola Barang Milik Daerah', 'golongan' => 'III/c'],
            ['nama' => 'Rina Marlina, A.Md.', 'nip' => '199002142014022006', 'jabatan' => 'Pengadministrasi Umum', 'golongan' => 'II/d'],
            ['nama' => 'Agus Setiawan', 'nip' => '198811052010011007', 'jabatan' => 'Pengemudi', 'golongan' => 'II/b'],
            ['nama' => 'Fitri Handayani, S.Ak.', 'nip' => '199306202019032008', 'jabatan' => 'Penata Laporan Keuangan', 'golongan' => 'III/a'],
            ['nama' => 'Hendra Gunawan, S.T.', 'nip' => '199109092017011009', 'jabatan' => 'Penelaah Teknis Kebijakan', 'golongan' => 'III/b'],
            ['nama' => 'Yusuf Maulana', 'nip' => '199512012020121010', 'jabatan' => 'Pengemudi', 'golongan' => 'II/a'],
        ];

        foreach ($data as $row) {
            Pegawai::updateOrCreate(['nip' => $row['nip']], $row);
        }
    }
}
