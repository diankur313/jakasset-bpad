<?php

namespace Tests\Feature;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Filament\Widgets\KendaraanDinasTerbaru;
use App\Filament\Widgets\KendaraanPerPerangkatDaerahChart;
use App\Filament\Widgets\KondisiKendaraanChart;
use App\Filament\Widgets\StatistikAsset;
use App\Filament\Widgets\StatusKendaraanChart;
use App\Models\Instansi;
use App\Models\KendaraanDinas;
use App\Models\MasterKendaraan;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\WarnaKendaraan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['email' => 'tester@jakasset-mail.com']));
    }

    /** 3 kendaraan: 2 Baik/Active (Disdik), 1 Rusak Berat/Standby (Dinkes). */
    private function dataContoh(): void
    {
        $warna = WarnaKendaraan::firstOrCreate(['nama' => 'Hitam']);
        $baik = MasterKendaraan::create([
            'merek' => 'Toyota', 'tipe_model' => 'Avanza', 'tahun_produksi' => 2022,
            'kondisi' => KondisiKendaraan::Baik, 'status' => StatusKendaraan::Active,
        ]);
        $rusak = MasterKendaraan::create([
            'merek' => 'Isuzu', 'tipe_model' => 'Elf', 'tahun_produksi' => 2015,
            'kondisi' => KondisiKendaraan::RusakBerat, 'status' => StatusKendaraan::Standby,
        ]);
        $disdik = Instansi::create(['nama' => 'Dinas Pendidikan', 'singkatan' => 'Disdik']);
        $dinkes = Instansi::create(['nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes']);
        Instansi::create(['nama' => 'Dinas Pariwisata']);

        $buat = function (string $nopol, MasterKendaraan $m, Instansi $i, string $nip) use ($warna): void {
            $p = Pegawai::create(['nama' => "Pegawai {$nip}", 'nip' => $nip, 'jabatan' => 'Staf', 'golongan' => 'III/a', 'instansi_id' => $i->id]);
            KendaraanDinas::create([
                'nomor_polisi' => $nopol, 'master_kendaraan_id' => $m->id, 'warna_kendaraan_id' => $warna->id,
                'instansi_id' => $i->id, 'pegawai_id' => $p->id,
            ]);
        };

        $buat('B 1 AA', $baik, $disdik, '199001012015031001');
        $buat('B 2 BB', $baik, $disdik, '199001012015031002');
        $buat('B 3 CC', $rusak, $dinkes, '199001012015031003');
        Pegawai::create(['nama' => 'Tanpa Kendaraan', 'nip' => '199001012015031004', 'jabatan' => 'Staf', 'golongan' => 'II/a', 'instansi_id' => $dinkes->id]);
    }

    public function test_dasbor_tampil_dengan_widget_data_real(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(StatistikAsset::class)
            ->assertSeeLivewire(KondisiKendaraanChart::class)
            ->assertSeeLivewire(StatusKendaraanChart::class)
            ->assertSeeLivewire(KendaraanPerPerangkatDaerahChart::class)
            ->assertSeeLivewire(KendaraanDinasTerbaru::class)
            ->assertDontSee('filamentphp.com');
    }

    public function test_statistik_dihitung_dari_database(): void
    {
        $this->dataContoh();

        Livewire::test(StatistikAsset::class)
            ->assertSeeInOrder(['Total Kendaraan Dinas', '3', '3 ditambahkan bulan ini'])
            ->assertSeeInOrder(['Kendaraan Active', '2', '67% dari seluruh kendaraan dinas'])
            ->assertSeeInOrder(['Perlu Perbaikan', '1', 'Rusak ringan 0 · Rusak berat 1'])
            ->assertSeeInOrder(['Pegawai', '4', '3 menjadi pemegang kendaraan'])
            ->assertSeeInOrder(['Perangkat Daerah', '3', '2 memiliki kendaraan dinas'])
            ->assertSeeInOrder(['Master Kendaraan', '2', '2 merek terdaftar']);

        $this->assertSame([0, 0, 0, 0, 0, 3], StatistikAsset::trenBulanan());
    }

    public function test_grafik_kondisi_status_dan_per_perangkat_daerah(): void
    {
        $this->dataContoh();

        $this->assertSame(['baik' => 2, 'rusak_ringan' => 0, 'rusak_berat' => 1], KondisiKendaraanChart::hitung());
        $this->assertSame(['active' => 2, 'inactive' => 0, 'standby' => 1], StatusKendaraanChart::hitung());

        $chart = Livewire::test(KendaraanPerPerangkatDaerahChart::class)->instance();
        $data = (fn () => $this->getData())->call($chart);

        $this->assertSame(['Disdik', 'Dinkes', 'Dinas Pariwisata'], $data['labels']);
        $this->assertSame([2, 1, 0], $data['datasets'][0]['data']);
        $this->assertSame([2, 2, 0], $data['datasets'][1]['data']);
    }

    public function test_tabel_kendaraan_terbaru_maksimal_lima(): void
    {
        $this->dataContoh();
        $master = MasterKendaraan::first();
        $pegawai = Pegawai::first();
        foreach (range(4, 7) as $n) {
            KendaraanDinas::create([
                'nomor_polisi' => "B {$n} DD", 'master_kendaraan_id' => $master->id,
                'warna_kendaraan_id' => WarnaKendaraan::first()->id,
                'instansi_id' => $pegawai->instansi_id, 'pegawai_id' => $pegawai->id,
            ]);
        }

        Livewire::test(KendaraanDinasTerbaru::class)
            ->assertCanSeeTableRecords(KendaraanDinas::orderByDesc('id')->limit(5)->get(), inOrder: true)
            ->assertCanNotSeeTableRecords(KendaraanDinas::orderBy('id')->limit(2)->get())
            ->assertSee('Disdik');
    }
}
