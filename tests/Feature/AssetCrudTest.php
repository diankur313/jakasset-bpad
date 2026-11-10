<?php

namespace Tests\Feature;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Filament\Resources\Instansi\Pages\ListInstansi;
use App\Filament\Resources\KendaraanDinas\Pages\CreateKendaraanDinas;
use App\Filament\Resources\KendaraanDinas\Pages\EditKendaraanDinas;
use App\Filament\Resources\KendaraanDinas\Pages\ListKendaraanDinas;
use App\Filament\Resources\MasterKendaraan\Pages\CreateMasterKendaraan;
use App\Filament\Resources\MasterKendaraan\Pages\ListMasterKendaraan;
use App\Filament\Resources\Pegawai\Pages\CreatePegawai;
use App\Filament\Resources\Pegawai\Pages\EditPegawai;
use App\Filament\Resources\Pegawai\Pages\ListPegawai;
use App\Filament\Resources\PerangkatDaerah\PerangkatDaerahResource;
use App\Models\Instansi;
use App\Models\KendaraanDinas;
use App\Models\MasterKendaraan;
use App\Models\Pegawai;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['email' => 'tester@jakasset-mail.com']));
    }

    private function master(array $attrs = []): MasterKendaraan
    {
        return MasterKendaraan::create($attrs + [
            'merek' => 'Toyota',
            'tipe_model' => 'Innova Zenix',
            'tahun_produksi' => 2023,
            'kondisi' => KondisiKendaraan::Baik,
            'status' => StatusKendaraan::Active,
        ]);
    }

    private function instansi(string $nama = 'Dinas Pendidikan'): Instansi
    {
        return Instansi::create(['nama' => $nama]);
    }

    private function pegawai(string $nip = '199001012015031001'): Pegawai
    {
        return Pegawai::create(['nama' => 'Budi', 'nip' => $nip, 'jabatan' => 'Kepala Seksi', 'golongan' => 'III/c']);
    }

    private function kendaraan(MasterKendaraan $m, Instansi $i, ?Pegawai $p = null, string $nopol = 'B 1234 ABC'): KendaraanDinas
    {
        return KendaraanDinas::create([
            'nomor_polisi' => $nopol,
            'master_kendaraan_id' => $m->id,
            'warna' => 'Hitam',
            'kondisi' => KondisiKendaraan::Baik,
            'status' => StatusKendaraan::Active,
            'instansi_id' => $i->id,
            'pegawai_id' => $p?->id,
        ]);
    }

    public function test_semua_halaman_menu_bisa_dibuka(): void
    {
        $i = $this->instansi();
        $this->kendaraan($this->master(), $i, $this->pegawai());

        foreach ([
            '/admin/asset/kendaraan-dinas', '/admin/asset/kendaraan-dinas/create',
            '/admin/pegawai', '/admin/pegawai/create',
            '/admin/master-data/kendaraan', '/admin/master-data/kendaraan/create',
            '/admin/master-data/instansi', '/admin/master-data/instansi/create',
            '/admin/perangkat-daerah', "/admin/perangkat-daerah/{$i->id}",
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_tambah_dan_edit_pegawai(): void
    {
        Livewire::test(CreatePegawai::class)
            ->fillForm(['nama' => 'Siti', 'nip' => '198505052010012002', 'jabatan' => 'Analis', 'golongan' => 'III/a'])
            ->call('create')
            ->assertHasNoFormErrors();

        $p = Pegawai::where('nip', '198505052010012002')->firstOrFail();

        Livewire::test(EditPegawai::class, ['record' => $p->getRouteKey()])
            ->fillForm(['jabatan' => 'Kepala Bidang'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Kepala Bidang', $p->fresh()->jabatan);
    }

    public function test_nip_harus_18_digit_dan_unik(): void
    {
        $this->pegawai('199001012015031001');

        Livewire::test(CreatePegawai::class)
            ->fillForm(['nama' => 'X', 'nip' => '123', 'jabatan' => 'Staf', 'golongan' => 'II/a'])
            ->call('create')
            ->assertHasFormErrors(['nip']);

        Livewire::test(CreatePegawai::class)
            ->fillForm(['nama' => 'X', 'nip' => '199001012015031001', 'jabatan' => 'Staf', 'golongan' => 'II/a'])
            ->call('create')
            ->assertHasFormErrors(['nip' => 'unique']);
    }

    public function test_master_kendaraan_kombinasi_unik(): void
    {
        $this->master();

        Livewire::test(CreateMasterKendaraan::class)
            ->fillForm(['merek' => 'Toyota', 'tipe_model' => 'Innova Zenix', 'tahun_produksi' => 2023, 'kondisi' => 'baik', 'status' => 'active'])
            ->call('create')
            ->assertHasFormErrors(['tahun_produksi' => 'unique']);

        Livewire::test(CreateMasterKendaraan::class)
            ->fillForm(['merek' => 'Toyota', 'tipe_model' => 'Innova Zenix', 'tahun_produksi' => 2024, 'kondisi' => 'rusak_ringan', 'status' => 'standby'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_tambah_kendaraan_dinas_dengan_pilihan_berjenjang(): void
    {
        $m = $this->master(['kondisi' => KondisiKendaraan::RusakRingan, 'status' => StatusKendaraan::Standby]);
        $i = $this->instansi();
        $p = $this->pegawai();

        Livewire::test(CreateKendaraanDinas::class)
            ->fillForm(['merek' => 'Toyota'])
            ->fillForm(['tipe_model' => 'Innova Zenix'])
            ->fillForm(['master_kendaraan_id' => $m->id])
            // kondisi & status otomatis terisi dari master
            ->assertSchemaStateSet(['kondisi' => KondisiKendaraan::RusakRingan, 'status' => StatusKendaraan::Standby])
            ->fillForm(['nomor_polisi' => 'b 9999 xy', 'warna' => 'Putih', 'instansi_id' => $i->id, 'pegawai_id' => $p->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $k = KendaraanDinas::firstOrFail();
        $this->assertSame('B 9999 XY', $k->nomor_polisi);
        $this->assertSame($m->id, $k->master_kendaraan_id);

        // Halaman edit memuat ulang Merek & Tipe dari relasi master.
        Livewire::test(EditKendaraanDinas::class, ['record' => $k->getRouteKey()])
            ->assertSchemaStateSet(['merek' => 'Toyota', 'tipe_model' => 'Innova Zenix', 'master_kendaraan_id' => $m->id]);
    }

    public function test_hapus_master_kendaraan_yang_dipakai_diblokir_dengan_alert(): void
    {
        $m = $this->master();
        $this->kendaraan($m, $this->instansi());

        Livewire::test(ListMasterKendaraan::class)
            ->mountAction(TestAction::make('delete')->table($m))
            ->assertMountedActionModalSee(['Data tidak dapat dihapus', 'B 1234 ABC'])
            ->callMountedAction()
            ->assertNotified('Data tidak dapat dihapus');

        $this->assertModelExists($m);
    }

    public function test_hapus_master_kendaraan_tidak_dipakai_berhasil(): void
    {
        $m = $this->master();

        Livewire::test(ListMasterKendaraan::class)
            ->callAction(TestAction::make('delete')->table($m));

        $this->assertModelMissing($m);
    }

    public function test_hapus_pegawai_dan_instansi_yang_dipakai_diblokir(): void
    {
        $i = $this->instansi();
        $p = $this->pegawai();
        $this->kendaraan($this->master(), $i, $p);

        Livewire::test(ListPegawai::class)
            ->callAction(TestAction::make('delete')->table($p))
            ->assertNotified('Data tidak dapat dihapus');

        Livewire::test(ListInstansi::class)
            ->callAction(TestAction::make('delete')->table($i))
            ->assertNotified('Data tidak dapat dihapus');

        $this->assertModelExists($p);
        $this->assertModelExists($i);
    }

    public function test_hapus_massal_melewati_data_yang_dipakai(): void
    {
        $dipakai = $this->instansi('Dinas Kesehatan');
        $bebas = $this->instansi('Dinas PUPR');
        $this->kendaraan($this->master(), $dipakai);

        Livewire::test(ListInstansi::class)
            ->selectTableRecords([$dipakai, $bebas])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified('1 data dihapus, 1 data dilewati');

        $this->assertModelExists($dipakai);
        $this->assertModelMissing($bebas);
    }

    public function test_hapus_kendaraan_dinas(): void
    {
        $k = $this->kendaraan($this->master(), $this->instansi());

        Livewire::test(ListKendaraanDinas::class)
            ->callAction(TestAction::make('delete')->table($k));

        $this->assertModelMissing($k);
    }

    public function test_perangkat_daerah_hanya_lihat(): void
    {
        $i = $this->instansi();

        $this->assertFalse(PerangkatDaerahResource::canCreate());
        $this->assertFalse(PerangkatDaerahResource::canEdit($i));
        $this->assertFalse(PerangkatDaerahResource::canDelete($i));
        $this->assertFalse(PerangkatDaerahResource::hasPage('create'));
        $this->assertFalse(PerangkatDaerahResource::hasPage('edit'));
    }
}
