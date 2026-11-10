<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\Instansi\InstansiResource as FilamentInstansi;
use App\Http\Requests\Api\IdsRequest;
use App\Http\Requests\Api\InstansiRequest;
use App\Http\Resources\InstansiResource;
use App\Models\Instansi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Master Data > Instansi Pemilik (CRUD). */
class InstansiController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Instansi::query()->withCount(['pegawai', 'kendaraanDinas']);
        $this->cari($query, $request, ['nama', 'singkatan']);
        $this->urutkan($query, $request, ['nama', 'singkatan', 'created_at'], 'nama');

        return InstansiResource::collection($query->paginate($this->perHalaman($request))->withQueryString());
    }

    public function store(InstansiRequest $request): JsonResponse
    {
        $instansi = Instansi::create($request->validated());

        return (new InstansiResource($instansi->loadCount(['pegawai', 'kendaraanDinas'])))
            ->additional(['message' => 'Instansi berhasil ditambahkan.'])
            ->response()->setStatusCode(201);
    }

    public function show(Instansi $instansi): InstansiResource
    {
        return new InstansiResource($instansi->loadCount(['pegawai', 'kendaraanDinas']));
    }

    public function update(InstansiRequest $request, Instansi $instansi): InstansiResource
    {
        $instansi->update($request->validated());

        return (new InstansiResource($instansi->loadCount(['pegawai', 'kendaraanDinas'])))
            ->additional(['message' => 'Instansi berhasil diperbarui.']);
    }

    public function destroy(Instansi $instansi): JsonResponse
    {
        return $this->hapus($instansi, FilamentInstansi::DEPENDENCIES);
    }

    public function hapusMassal(IdsRequest $request): JsonResponse
    {
        return $this->hapusBanyak($request, Instansi::class, FilamentInstansi::DEPENDENCIES, 'nama');
    }
}
