<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\InstansiResource;
use App\Http\Resources\KendaraanDinasResource;
use App\Http\Resources\PegawaiResource;
use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Menu Perangkat Daerah: hanya lihat data (read-only).
 * Tambah/ubah/hapus dilakukan lewat endpoint /instansi (Master Data > Instansi Pemilik).
 */
class PerangkatDaerahController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Instansi::query()->withCount(['pegawai', 'kendaraanDinas']);
        $this->cari($query, $request, ['nama', 'singkatan']);
        $this->urutkan($query, $request, ['nama', 'singkatan'], 'nama');

        return InstansiResource::collection($query->paginate($this->perHalaman($request))->withQueryString());
    }

    public function show(Instansi $instansi): InstansiResource
    {
        return new InstansiResource($instansi->loadCount(['pegawai', 'kendaraanDinas']));
    }

    public function kendaraanDinas(Request $request, Instansi $instansi): AnonymousResourceCollection
    {
        $query = $instansi->kendaraanDinas()->getQuery()
            ->with(['masterKendaraan', 'warnaKendaraan', 'instansi', 'pegawai']);
        $this->urutkan($query, $request, ['nomor_asset', 'nomor_polisi', 'created_at'], 'nomor_asset');

        return KendaraanDinasResource::collection($query->paginate($this->perHalaman($request))->withQueryString());
    }

    public function pegawai(Request $request, Instansi $instansi): AnonymousResourceCollection
    {
        $query = $instansi->pegawai()->getQuery()->with('instansi')->withCount('kendaraanDinas');
        $this->urutkan($query, $request, ['nama', 'nip', 'golongan'], 'nama');

        return PegawaiResource::collection($query->paginate($this->perHalaman($request))->withQueryString());
    }
}
