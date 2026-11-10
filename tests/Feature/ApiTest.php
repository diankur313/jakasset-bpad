<?php

namespace Tests\Feature;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Models\Instansi;
use App\Models\KendaraanDinas;
use App\Models\MasterKendaraan;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\WarnaKendaraan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private function login(): User
    {
        $user = User::factory()->create(['email' => 'api@jakasset-mail.com']);
        Sanctum::actingAs($user);

        return $user;
    }

    private function instansi(string $nama = 'Dinas Pendidikan'): Instansi
    {
        return Instansi::firstOrCreate(['nama' => $nama]);
    }

    private function pegawai(?Instansi $instansi = null, string $nip = '199001012015031001'): Pegawai
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

    private function master(array $attrs = []): MasterKendaraan
    {
        $master = MasterKendaraan::create($attrs + [
            'merek' => 'Toyota', 'tipe_model' => 'Innova Zenix', 'tahun_produksi' => 2023,
            'kondisi' => KondisiKendaraan::Baik, 'status' => StatusKendaraan::Active,
        ]);
        $master->warna()->sync([$this->warna()->id]);

        return $master;
    }

    private function kendaraan(string $nopol = 'B 1234 ABC'): KendaraanDinas
    {
        $instansi = $this->instansi();
        $pegawai = Pegawai::where('instansi_id', $instansi->id)->first() ?? $this->pegawai($instansi);

        return KendaraanDinas::create([
            'nomor_polisi' => $nopol,
            'master_kendaraan_id' => (MasterKendaraan::first() ?? $this->master())->id,
            'warna_kendaraan_id' => $this->warna()->id,
            'instansi_id' => $instansi->id,
            'pegawai_id' => $pegawai->id,
        ]);
    }

    public function test_login_menghasilkan_token_dan_token_bisa_dipakai_lalu_dicabut(): void
    {
        User::factory()->create(['email' => 'admin@jakasset-mail.com', 'password' => 'rahasia-test']);

        $this->postJson('/api/v1/auth/login', ['email' => 'admin@jakasset-mail.com', 'password' => 'salah'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Email atau password salah.');

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@jakasset-mail.com', 'password' => 'rahasia-test', 'nama_perangkat' => 'Postman',
        ])->assertOk()->assertJsonPath('token_type', 'Bearer')->json('token');

        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertOk()->assertJsonPath('data.email', 'admin@jakasset-mail.com');

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_ditolak_untuk_domain_lain_dan_endpoint_butuh_token(): void
    {
        User::factory()->create(['email' => 'orang@gmail.com', 'password' => 'rahasia-test']);

        $this->postJson('/api/v1/auth/login', ['email' => 'orang@gmail.com', 'password' => 'rahasia-test'])
            ->assertForbidden();

        // Tanpa header Accept pun tetap dijawab JSON.
        $this->get('/api/v1/kendaraan-dinas')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Belum login atau token tidak valid. Login di POST /api/v1/auth/login.');
    }

    public function test_crud_instansi_dan_hapus_diblokir_bila_masih_dipakai(): void
    {
        $this->login();

        $id = $this->postJson('/api/v1/instansi', ['nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes'])
            ->assertCreated()->assertJsonPath('data.nama', 'Dinas Kesehatan')->json('data.id');

        $this->postJson('/api/v1/instansi', ['nama' => 'Dinas Kesehatan'])
            ->assertStatus(422)->assertJsonPath('errors.nama.0', 'Nama instansi ini sudah terdaftar.');

        // PATCH = ubah sebagian.
        $this->patchJson("/api/v1/instansi/{$id}", ['alamat' => 'Jl. Kesehatan No. 1'])
            ->assertOk()->assertJsonPath('data.nama', 'Dinas Kesehatan')->assertJsonPath('data.alamat', 'Jl. Kesehatan No. 1');

        // PUT = ubah penuh, field wajib harus dikirim.
        $this->putJson("/api/v1/instansi/{$id}", ['alamat' => 'x'])
            ->assertStatus(422)->assertJsonPath('errors.nama.0', 'Nama instansi wajib diisi.');

        $this->getJson('/api/v1/instansi?search=kesehatan')->assertOk()->assertJsonCount(1, 'data');

        $this->pegawai(Instansi::find($id));
        $this->deleteJson("/api/v1/instansi/{$id}")
            ->assertStatus(409)
            ->assertJsonPath('dependensi.0.data', 'Pegawai')
            ->assertJsonPath('dependensi.0.contoh.0', 'Budi');

        $kosong = $this->instansi('Dinas Pariwisata');
        $this->deleteJson("/api/v1/instansi/{$kosong->id}")->assertOk();
        $this->getJson("/api/v1/instansi/{$kosong->id}")->assertNotFound()->assertJsonPath('message', 'Data tidak ditemukan.');
    }

    public function test_pegawai_validasi_dan_tidak_bisa_pindah_instansi_saat_memegang_kendaraan(): void
    {
        $this->login();
        $disdik = $this->instansi();

        $this->postJson('/api/v1/pegawai', [
            'nama' => 'Siti', 'nip' => '123', 'jabatan' => 'Staf', 'golongan' => 'V/a', 'instansi_id' => 999,
        ])->assertStatus(422)->assertJsonValidationErrors(['nip', 'golongan', 'instansi_id'])
            ->assertJsonPath('errors.nip', fn (array $pesan) => in_array('NIP harus 18 digit angka.', $pesan));

        $id = $this->postJson('/api/v1/pegawai', [
            'nama' => 'Siti', 'nip' => '199202022016042002', 'jabatan' => 'Staf', 'golongan' => 'III/a', 'instansi_id' => $disdik->id,
        ])->assertCreated()->assertJsonPath('data.instansi.nama', 'Dinas Pendidikan')->json('data.id');

        $kendaraan = $this->kendaraan();
        $kendaraan->update(['pegawai_id' => $id]);

        $dinkes = $this->instansi('Dinas Kesehatan');
        $this->patchJson("/api/v1/pegawai/{$id}", ['instansi_id' => $dinkes->id])
            ->assertStatus(422)
            ->assertJsonPath('errors.instansi_id.0', fn (string $pesan) => str_contains($pesan, 'B 1234 ABC'));

        $this->deleteJson("/api/v1/pegawai/{$id}")->assertStatus(409);
        $this->getJson("/api/v1/pegawai?instansi_id={$disdik->id}&golongan=III/a")->assertJsonCount(1, 'data');
    }

    public function test_master_kendaraan_dengan_warna_tersedia(): void
    {
        $this->login();
        $hitam = $this->warna('Hitam');
        $putih = $this->warna('Putih');

        $this->postJson('/api/v1/master-kendaraan', [
            'merek' => 'Toyota', 'tipe_model' => 'Avanza', 'tahun_produksi' => 2022,
            'kondisi' => 'bagus', 'status' => 'active', 'warna' => [],
        ])->assertStatus(422)->assertJsonValidationErrors(['kondisi', 'warna']);

        $id = $this->postJson('/api/v1/master-kendaraan', [
            'merek' => 'Toyota', 'tipe_model' => 'Avanza', 'tahun_produksi' => 2022,
            'kondisi' => 'baik', 'status' => 'standby', 'warna' => [$hitam->id, $putih->id],
        ])->assertCreated()
            ->assertJsonPath('data.status.label', 'Standby')
            ->assertJsonCount(2, 'data.warna')
            ->json('data.id');

        $this->postJson('/api/v1/master-kendaraan', [
            'merek' => 'Toyota', 'tipe_model' => 'Avanza', 'tahun_produksi' => 2022,
            'kondisi' => 'baik', 'status' => 'active', 'warna' => [$hitam->id],
        ])->assertStatus(422)->assertJsonPath('errors.tahun_produksi.0', 'Kombinasi Merek, Tipe / Model, dan Tahun Produksi ini sudah ada.');

        // Warna yang dipakai kendaraan dinas tidak bisa dikeluarkan dari Warna Tersedia.
        KendaraanDinas::create([
            'nomor_polisi' => 'B 1 AA', 'master_kendaraan_id' => $id, 'warna_kendaraan_id' => $putih->id,
            'instansi_id' => $this->instansi()->id, 'pegawai_id' => $this->pegawai()->id,
        ]);
        $this->patchJson("/api/v1/master-kendaraan/{$id}", ['warna' => [$hitam->id]])
            ->assertStatus(422)->assertJsonValidationErrors('warna');

        // PATCH tanpa field warna tidak menghapus warna yang ada.
        $this->patchJson("/api/v1/master-kendaraan/{$id}", ['kondisi' => 'rusak_ringan'])
            ->assertOk()->assertJsonPath('data.kondisi.value', 'rusak_ringan')->assertJsonCount(2, 'data.warna');

        $this->deleteJson("/api/v1/master-kendaraan/{$id}")->assertStatus(409);
        $this->getJson('/api/v1/master-kendaraan?status=standby')->assertJsonCount(1, 'data');
    }

    public function test_warna_kendaraan_dirapikan_dan_tidak_dobel(): void
    {
        $this->login();

        $this->postJson('/api/v1/warna-kendaraan', ['nama' => '  biru   dongker ', 'kode_hex' => '#1F2A44'])
            ->assertCreated()->assertJsonPath('data.nama', 'Biru Dongker');

        $this->postJson('/api/v1/warna-kendaraan', ['nama' => 'BIRU DONGKER'])
            ->assertStatus(422)->assertJsonPath('errors.nama.0', 'Warna ini sudah ada. Pilih dari daftar Warna Tersedia.');

        $master = $this->master();
        $this->getJson("/api/v1/warna-kendaraan?master_kendaraan_id={$master->id}")
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama', 'Hitam');
    }

    public function test_crud_kendaraan_dinas_mengikuti_aturan_form(): void
    {
        $this->login();
        $master = $this->master();
        $disdik = $this->instansi();
        $pegawai = $this->pegawai($disdik);
        $pegawaiDinkes = $this->pegawai($this->instansi('Dinas Kesehatan'), '198505052010011003');
        $merah = $this->warna('Merah');

        $this->postJson('/api/v1/kendaraan-dinas', [
            'nomor_polisi' => 'B 1234 ABC', 'master_kendaraan_id' => $master->id,
            'warna_kendaraan_id' => $merah->id, 'instansi_id' => $disdik->id, 'pegawai_id' => $pegawaiDinkes->id,
        ])->assertStatus(422)
            ->assertJsonPath('errors.warna_kendaraan_id.0', 'Warna ini tidak tersedia untuk master kendaraan yang dipilih.')
            ->assertJsonPath('errors.pegawai_id.0', 'Pegawai ini tidak terdaftar di perangkat daerah yang dipilih.');

        $response = $this->postJson('/api/v1/kendaraan-dinas', [
            'nomor_polisi' => ' b  1234   abc ', 'master_kendaraan_id' => $master->id,
            'warna_kendaraan_id' => $this->warna()->id, 'instansi_id' => $disdik->id, 'pegawai_id' => $pegawai->id,
            'nomor_asset' => 'DIABAIKAN',
        ])->assertCreated()
            ->assertJsonPath('data.nomor_polisi', 'B 1234 ABC')
            ->assertJsonPath('data.kondisi.value', 'baik')
            ->assertJsonPath('data.pemegang.nip', '199001012015031001');

        $id = $response->json('data.id');
        $nomorAsset = $response->json('data.nomor_asset');
        $this->assertMatchesRegularExpression('#^\d{4}-\d{2}-\d{2}/KENDARAAN/\d{4}$#', $nomorAsset);

        $this->postJson('/api/v1/kendaraan-dinas', [
            'nomor_polisi' => 'B 1234 ABC', 'master_kendaraan_id' => $master->id,
            'warna_kendaraan_id' => $this->warna()->id, 'instansi_id' => $disdik->id, 'pegawai_id' => $pegawai->id,
        ])->assertStatus(422)->assertJsonPath('errors.nomor_polisi.0', 'Nomor polisi ini sudah terdaftar.');

        // Kondisi & status mengikuti master; nomor asset tidak bisa diubah.
        $master->update(['status' => StatusKendaraan::Inactive]);
        $this->patchJson("/api/v1/kendaraan-dinas/{$id}", ['nomor_polisi' => 'B 9999 XYZ', 'nomor_asset' => 'X'])
            ->assertOk()
            ->assertJsonPath('data.nomor_polisi', 'B 9999 XYZ')
            ->assertJsonPath('data.nomor_asset', $nomorAsset)
            ->assertJsonPath('data.status.value', 'inactive');

        $this->getJson('/api/v1/kendaraan-dinas?nomor_asset='.urlencode($nomorAsset))->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/kendaraan-dinas?status=active')->assertJsonCount(0, 'data');

        $this->deleteJson("/api/v1/kendaraan-dinas/{$id}")->assertOk();
        $this->assertDatabaseCount('kendaraan_dinas', 0);
    }

    public function test_hapus_massal_melewati_data_yang_masih_dipakai(): void
    {
        $this->login();
        $dipakai = $this->instansi();
        $this->pegawai($dipakai);
        $kosong = $this->instansi('Dinas Pariwisata');

        $this->postJson('/api/v1/instansi/hapus-massal', ['ids' => [$dipakai->id, $kosong->id, 9999]])
            ->assertOk()
            ->assertJsonPath('dihapus', [$kosong->id])
            ->assertJsonPath('dilewati.0.nama', 'Dinas Pendidikan')
            ->assertJsonPath('tidak_ditemukan', [9999]);
    }

    public function test_download_label_satuan_dan_batch(): void
    {
        $this->login();
        $satu = $this->kendaraan('B 1 AA');
        $dua = $this->kendaraan('B 2 BB');

        $satuan = $this->get("/api/v1/kendaraan-dinas/{$satu->id}/label")->assertOk();
        $this->assertStringStartsWith('%PDF', $satuan->streamedContent());
        $this->assertStringContainsString('label-', $satuan->headers->get('content-disposition'));

        $batch = $this->post('/api/v1/kendaraan-dinas/label', ['ids' => [$satu->id, $dua->id]])->assertOk();
        $this->assertStringStartsWith('%PDF', $batch->streamedContent());
        $this->assertStringContainsString('label-kendaraan-dinas-', $batch->headers->get('content-disposition'));

        $this->postJson('/api/v1/kendaraan-dinas/label', ['ids' => [9999]])->assertNotFound();
    }

    public function test_perangkat_daerah_hanya_bisa_dilihat(): void
    {
        $this->login();
        $kendaraan = $this->kendaraan();
        $id = $kendaraan->instansi_id;

        $this->getJson("/api/v1/perangkat-daerah/{$id}")
            ->assertOk()->assertJsonPath('data.jumlah_kendaraan_dinas', 1)->assertJsonPath('data.jumlah_pegawai', 1);
        $this->getJson("/api/v1/perangkat-daerah/{$id}/kendaraan-dinas")->assertJsonPath('data.0.nomor_polisi', 'B 1234 ABC');
        $this->getJson("/api/v1/perangkat-daerah/{$id}/pegawai")->assertJsonPath('data.0.nama', 'Budi');

        $this->postJson('/api/v1/perangkat-daerah', ['nama' => 'X'])->assertStatus(405);
        $this->deleteJson("/api/v1/perangkat-daerah/{$id}")->assertStatus(405);
    }

    public function test_referensi_dan_paginasi(): void
    {
        $this->login();

        $this->getJson('/api/v1/referensi')
            ->assertOk()
            ->assertJsonPath('data.kondisi.1', ['value' => 'rusak_ringan', 'label' => 'Rusak Ringan'])
            ->assertJsonCount(17, 'data.golongan');

        foreach (range(1, 3) as $i) {
            $this->instansi("Dinas {$i}");
        }

        $this->getJson('/api/v1/instansi?per_page=2&sort=-nama')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nama', 'Dinas 3')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);
    }
}
