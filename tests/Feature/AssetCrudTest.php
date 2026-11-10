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
use App\Models\WarnaKendaraan;
use App\Filament\Resources\MasterKendaraan\Pages\EditMasterKendaraan;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
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
        return Instansi::firstOrCreate(['nama' => $nama]);
    }

    private function pegawai(string $nip = '199001012015031001', ?Instansi $instansi = null): Pegawai
    {
        return Pegawai::create([
            'nama' => 'Budi', 'nip' => $nip, 'jabatan' => 'Kepala Seksi', 'golongan' => 'III/c',
            'instansi_id' => ($instansi ?? $this->instansi())->id,
        ]);
    }

    private function warna(string $nama = 'Hitam'): WarnaKendaraan
    {
        return WarnaKendaraan::firstOrCreate(['nama' => $nama]);
    }

    private function kendaraan(MasterKendaraan $m, Instansi $i, ?Pegawai $p = null, string $nopol = 'B 1234 ABC'): KendaraanDinas
    {
        $m->warna()->syncWithoutDetaching([$this->warna()->id]);

        return KendaraanDinas::create([
            'nomor_polisi' => $nopol,
            'master_kendaraan_id' => $m->id,
            'warna_kendaraan_id' => $this->warna()->id,
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
            ->fillForm(['nama' => 'Siti', 'nip' => '198505052010012002', 'jabatan' => 'Analis', 'golongan' => 'III/a', 'instansi_id' => $this->instansi()->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $p = Pegawai::where('nip', '198505052010012002')->firstOrFail();

        Livewire::test(EditPegawai::class, ['record' => $p->getRouteKey()])
            ->fillForm(['jabatan' => 'Kepala Bidang'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Kepala Bidang', $p->fresh()->jabatan);
        $this->assertSame('Dinas Pendidikan', $p->fresh()->instansi->nama);
    }

    public function test_pegawai_wajib_punya_instansi(): void
    {
        Livewire::test(CreatePegawai::class)
            ->fillForm(['nama' => 'Tanpa Instansi', 'nip' => '198505052010012003', 'jabatan' => 'Staf', 'golongan' => 'II/a'])
            ->call('create')
            ->assertHasFormErrors(['instansi_id' => 'required']);
    }

    public function test_pemegang_wajib_dan_harus_dari_instansi_pemilik(): void
    {
        $m = $this->master();
        $m->warna()->attach($this->warna());
        $disdik = $this->instansi('Dinas Pendidikan');
        $dinkes = $this->instansi('Dinas Kesehatan');
        $pegawaiDisdik = $this->pegawai('199001012015031001', $disdik);
        $pegawaiDinkes = $this->pegawai('199001012015031002', $dinkes);

        $form = fn () => Livewire::test(CreateKendaraanDinas::class)
            ->fillForm(['merek' => 'Toyota'])
            ->fillForm(['tipe_model' => 'Innova Zenix'])
            ->fillForm(['master_kendaraan_id' => $m->id])
            ->fillForm(['nomor_polisi' => 'B 555 PD', 'instansi_id' => $disdik->id]);

        // Pemegang wajib diisi.
        $form()->call('create')->assertHasFormErrors(['pegawai_id' => 'required']);

        // Pegawai instansi lain ditolak.
        $form()->fillForm(['pegawai_id' => $pegawaiDinkes->id])->call('create')->assertHasFormErrors(['pegawai_id' => 'in']);

        // Pegawai instansi yang sama diterima.
        $form()->fillForm(['pegawai_id' => $pegawaiDisdik->id])->call('create')->assertHasNoFormErrors();

        $this->assertSame($pegawaiDisdik->id, KendaraanDinas::firstOrFail()->pegawai_id);
    }

    public function test_ganti_perangkat_daerah_mengosongkan_pemegang(): void
    {
        $disdik = $this->instansi('Dinas Pendidikan');
        $dinkes = $this->instansi('Dinas Kesehatan');
        $p = $this->pegawai('199001012015031001', $disdik);

        Livewire::test(CreateKendaraanDinas::class)
            ->fillForm(['instansi_id' => $disdik->id])
            ->fillForm(['pegawai_id' => $p->id])
            ->fillForm(['instansi_id' => $dinkes->id])
            ->assertSchemaStateSet(['pegawai_id' => null]);
    }

    public function test_pegawai_pemegang_kendaraan_tidak_bisa_pindah_instansi(): void
    {
        $disdik = $this->instansi('Dinas Pendidikan');
        $dinkes = $this->instansi('Dinas Kesehatan');
        $p = $this->pegawai('199001012015031001', $disdik);
        $this->kendaraan($this->master(), $disdik, $p);

        Livewire::test(EditPegawai::class, ['record' => $p->getRouteKey()])
            ->fillForm(['instansi_id' => $dinkes->id])
            ->call('save')
            ->assertHasFormErrors(['instansi_id']);

        $this->assertSame($disdik->id, $p->fresh()->instansi_id);
    }

    public function test_hapus_instansi_yang_punya_pegawai_diblokir(): void
    {
        $i = $this->instansi('Dinas Kesehatan');
        $this->pegawai('199001012015031001', $i);

        Livewire::test(ListInstansi::class)
            ->callAction(TestAction::make('delete')->table($i))
            ->assertNotified('Data tidak dapat dihapus');

        $this->assertModelExists($i);
    }

    public function test_nip_harus_18_digit_dan_unik(): void
    {
        $this->pegawai('199001012015031001');

        Livewire::test(CreatePegawai::class)
            ->fillForm(['nama' => 'X', 'nip' => '123', 'jabatan' => 'Staf', 'golongan' => 'II/a', 'instansi_id' => $this->instansi()->id])
            ->call('create')
            ->assertHasFormErrors(['nip']);

        Livewire::test(CreatePegawai::class)
            ->fillForm(['nama' => 'X', 'nip' => '199001012015031001', 'jabatan' => 'Staf', 'golongan' => 'II/a', 'instansi_id' => $this->instansi()->id])
            ->call('create')
            ->assertHasFormErrors(['nip' => 'unique']);
    }

    public function test_master_kendaraan_kombinasi_unik(): void
    {
        $this->master();
        $w = [$this->warna()->id];

        Livewire::test(CreateMasterKendaraan::class)
            ->fillForm(['merek' => 'Toyota', 'tipe_model' => 'Innova Zenix', 'tahun_produksi' => 2023, 'kondisi' => 'baik', 'status' => 'active', 'warna' => $w])
            ->call('create')
            ->assertHasFormErrors(['tahun_produksi' => 'unique']);

        Livewire::test(CreateMasterKendaraan::class)
            ->fillForm(['merek' => 'Toyota', 'tipe_model' => 'Innova Zenix', 'tahun_produksi' => 2024, 'kondisi' => 'rusak_ringan', 'status' => 'standby', 'warna' => $w])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_tambah_kendaraan_dinas_dengan_pilihan_berjenjang(): void
    {
        $m = $this->master(['kondisi' => KondisiKendaraan::RusakRingan, 'status' => StatusKendaraan::Standby]);
        $putih = $this->warna('Putih');
        $m->warna()->attach($putih);
        $i = $this->instansi();
        $p = $this->pegawai();

        Livewire::test(CreateKendaraanDinas::class)
            ->fillForm(['merek' => 'Toyota'])
            ->fillForm(['tipe_model' => 'Innova Zenix'])
            ->fillForm(['master_kendaraan_id' => $m->id])
            // kondisi & status ditampilkan dari master; warna terisi karena master hanya punya satu warna
            ->assertSchemaStateSet([
                'kondisi' => KondisiKendaraan::RusakRingan,
                'status' => StatusKendaraan::Standby,
                'warna_kendaraan_id' => $putih->id,
            ])
            ->fillForm(['nomor_polisi' => 'b 9999 xy', 'instansi_id' => $i->id, 'pegawai_id' => $p->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $k = KendaraanDinas::firstOrFail();
        $this->assertSame('B 9999 XY', $k->nomor_polisi);
        $this->assertSame($m->id, $k->master_kendaraan_id);
        $this->assertMatchesRegularExpression('#^\d{4}-\d{2}-\d{2}/KENDARAAN/\d{4}$#', $k->nomor_asset);

        // Halaman edit memuat ulang Merek, Tipe, Kondisi & Status dari relasi master.
        Livewire::test(EditKendaraanDinas::class, ['record' => $k->getRouteKey()])
            ->assertSchemaStateSet([
                'merek' => 'Toyota', 'tipe_model' => 'Innova Zenix', 'master_kendaraan_id' => $m->id,
                'kondisi' => KondisiKendaraan::RusakRingan, 'status' => StatusKendaraan::Standby,
            ]);
    }

    public function test_kondisi_status_kendaraan_dinas_tidak_bisa_dipilih_dan_mengikuti_master(): void
    {
        $m = $this->master(['kondisi' => KondisiKendaraan::Baik, 'status' => StatusKendaraan::Active]);
        $k = $this->kendaraan($m, $this->instansi(), $this->pegawai());

        // Field terkunci di form tambah & ubah.
        Livewire::test(CreateKendaraanDinas::class)
            ->assertFormFieldDisabled('kondisi')
            ->assertFormFieldDisabled('status');

        Livewire::test(EditKendaraanDinas::class, ['record' => $k->getRouteKey()])
            ->assertFormFieldDisabled('kondisi')
            ->assertFormFieldDisabled('status');

        // Tidak ada kolom kondisi/status di tabel kendaraan_dinas: nilainya dibaca dari master.
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumns('kendaraan_dinas', ['kondisi']));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumns('kendaraan_dinas', ['status']));

        // Mengubah master langsung tercermin di kendaraan dinas.
        $m->update(['kondisi' => KondisiKendaraan::RusakBerat, 'status' => StatusKendaraan::Inactive]);
        $this->assertSame(KondisiKendaraan::RusakBerat, $k->fresh()->masterKendaraan->kondisi);

        Livewire::test(ListKendaraanDinas::class)
            ->filterTable('status', StatusKendaraan::Inactive->value)
            ->assertCanSeeTableRecords([$k]);

        Livewire::test(ListKendaraanDinas::class)
            ->filterTable('status', StatusKendaraan::Active->value)
            ->assertCanNotSeeTableRecords([$k]);
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

    public function test_nomor_asset_otomatis_berurutan_per_hari(): void
    {
        $m = $this->master();
        $i = $this->instansi();

        Carbon::setTestNow(Carbon::parse('2026-11-10 09:00', 'Asia/Jakarta'));
        $a = $this->kendaraan($m, $i, nopol: 'B 1 AA');
        $b = $this->kendaraan($m, $i, nopol: 'B 2 AA');

        $this->assertSame('2026-11-10/KENDARAAN/0001', $a->nomor_asset);
        $this->assertSame('2026-11-10/KENDARAAN/0002', $b->nomor_asset);

        // Nomor yang sudah terpakai tidak dipakai ulang walaupun datanya dihapus.
        $b->delete();
        $this->assertSame('2026-11-10/KENDARAAN/0003', $this->kendaraan($m, $i, nopol: 'B 3 AA')->nomor_asset);

        // Hari berikutnya mulai lagi dari 0001.
        Carbon::setTestNow(Carbon::parse('2026-11-11 08:00', 'Asia/Jakarta'));
        $this->assertSame('2026-11-11/KENDARAAN/0001', $this->kendaraan($m, $i, nopol: 'B 4 AA')->nomor_asset);

        Carbon::setTestNow();
    }

    public function test_tanggal_nomor_asset_memakai_waktu_jakarta(): void
    {
        // 23:30 UTC tanggal 10 = 06:30 WIB tanggal 11.
        Carbon::setTestNow(Carbon::parse('2026-11-10 23:30', 'UTC'));

        $k = $this->kendaraan($this->master(), $this->instansi());

        $this->assertSame('2026-11-11/KENDARAAN/0001', $k->nomor_asset);

        Carbon::setTestNow();
    }

    public function test_nomor_asset_tidak_bisa_diubah(): void
    {
        $k = $this->kendaraan($this->master(), $this->instansi());
        $asli = $k->nomor_asset;

        $k->nomor_asset = '2000-01-01/KENDARAAN/9999';
        $k->save();
        $this->assertSame($asli, $k->fresh()->nomor_asset);

        $merah = $this->warna('Merah');
        $k->masterKendaraan->warna()->attach($merah);
        $k->update(['nomor_asset' => 'X', 'warna_kendaraan_id' => $merah->id]);
        $this->assertSame($asli, $k->fresh()->nomor_asset);
        $this->assertSame('Merah', $k->fresh()->warnaKendaraan->nama);

        Livewire::test(EditKendaraanDinas::class, ['record' => $k->getRouteKey()])
            ->assertFormFieldDoesNotExist('nomor_asset');

        // Field juga tidak ada di form tambah.
        Livewire::test(CreateKendaraanDinas::class)
            ->assertFormFieldDoesNotExist('nomor_asset');
    }

    public function test_warna_tersedia_disimpan_di_form_master_kendaraan(): void
    {
        $hitam = $this->warna('Hitam');
        $putih = $this->warna('Putih');

        Livewire::test(CreateMasterKendaraan::class)
            ->fillForm([
                'merek' => 'Honda', 'tipe_model' => 'CR-V', 'tahun_produksi' => 2024,
                'kondisi' => 'baik', 'status' => 'active', 'warna' => [$hitam->id, $putih->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $m = MasterKendaraan::where('tipe_model', 'CR-V')->firstOrFail();
        $this->assertEqualsCanonicalizing(['Hitam', 'Putih'], $m->warna->pluck('nama')->all());

        // Warna Tersedia wajib diisi.
        Livewire::test(CreateMasterKendaraan::class)
            ->fillForm(['merek' => 'Honda', 'tipe_model' => 'HR-V', 'tahun_produksi' => 2024, 'kondisi' => 'baik', 'status' => 'active', 'warna' => []])
            ->call('create')
            ->assertHasFormErrors(['warna' => 'required']);
    }

    public function test_tambah_warna_baru_dari_form_master_dirapikan_dan_tidak_boleh_dobel(): void
    {
        $this->warna('Hitam');

        // Tambah warna baru lewat tombol + di field Warna Tersedia.
        Livewire::test(CreateMasterKendaraan::class)
            ->callAction(TestAction::make('createOption')->schemaComponent('warna'), data: ['nama' => '  biru   dongker ', 'kode_hex' => '#1A237E'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('warna_kendaraan', ['nama' => 'Biru Dongker', 'kode_hex' => '#1A237E']);

        // Penulisan berbeda dari warna yang sudah ada tetap ditolak.
        Livewire::test(CreateMasterKendaraan::class)
            ->callAction(TestAction::make('createOption')->schemaComponent('warna'), data: ['nama' => 'HITAM'])
            ->assertHasActionErrors(['nama']);

        $this->assertSame(1, WarnaKendaraan::where('nama', 'Hitam')->count());
    }

    public function test_warna_terpakai_tidak_bisa_dihapus_dari_master(): void
    {
        $m = $this->master();
        $this->kendaraan($m, $this->instansi()); // memakai warna Hitam
        $putih = $this->warna('Putih');
        $m->warna()->attach($putih);

        Livewire::test(EditMasterKendaraan::class, ['record' => $m->getRouteKey()])
            ->fillForm(['warna' => [$putih->id]])
            ->call('save')
            ->assertHasFormErrors(['warna']);

        $this->assertTrue($m->fresh()->warna->contains('nama', 'Hitam'));

        // Warna yang tidak dipakai boleh dihapus.
        Livewire::test(EditMasterKendaraan::class, ['record' => $m->getRouteKey()])
            ->fillForm(['warna' => [$this->warna('Hitam')->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($m->fresh()->warna->contains('nama', 'Putih'));
    }

    public function test_warna_kendaraan_dinas_harus_dari_warna_tersedia_master(): void
    {
        $m = $this->master();
        $m->warna()->attach([$this->warna('Hitam')->id, $this->warna('Putih')->id]);
        $merah = $this->warna('Merah'); // tidak tersedia untuk master ini

        Livewire::test(CreateKendaraanDinas::class)
            ->fillForm(['merek' => 'Toyota'])
            ->fillForm(['tipe_model' => 'Innova Zenix'])
            ->fillForm(['master_kendaraan_id' => $m->id])
            ->assertSchemaStateSet(['warna_kendaraan_id' => null]) // lebih dari satu warna: tidak dipilih otomatis
            ->fillForm(['nomor_polisi' => 'B 777 X', 'warna_kendaraan_id' => $merah->id, 'instansi_id' => $this->instansi()->id])
            ->call('create')
            ->assertHasFormErrors(['warna_kendaraan_id']);

        $this->assertSame(0, KendaraanDinas::count());
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
