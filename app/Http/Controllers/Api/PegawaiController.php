<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\Pegawai\PegawaiResource as FilamentPegawai;
use App\Http\Requests\Api\IdsRequest;
use App\Http\Requests\Api\PegawaiRequest;
use App\Http\Resources\PegawaiResource;
use App\Models\Pegawai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Menu Pegawai (CRUD). */
class PegawaiController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pegawai::query()->with('instansi')->withCount('kendaraanDinas');
        $this->cari($query, $request, ['nama', 'nip', 'jabatan']);
        $query->when($request->filled('instansi_id'), fn ($q) => $q->where('instansi_id', $request->integer('instansi_id')));
        $query->when($request->filled('golongan'), fn ($q) => $q->where('golongan', $request->query('golongan')));
        $this->urutkan($query, $request, ['nama', 'nip', 'jabatan', 'golongan', 'created_at'], 'nama');

        return PegawaiResource::collection($query->paginate($this->perHalaman($request))->withQueryString());
    }

    public function store(PegawaiRequest $request): JsonResponse
    {
        $pegawai = Pegawai::create($request->validated());

        return (new PegawaiResource($pegawai->load('instansi')->loadCount('kendaraanDinas')))
            ->additional(['message' => 'Pegawai berhasil ditambahkan.'])
            ->response()->setStatusCode(201);
    }

    public function show(Pegawai $pegawai): PegawaiResource
    {
        return new PegawaiResource($pegawai->load('instansi')->loadCount('kendaraanDinas'));
    }

    public function update(PegawaiRequest $request, Pegawai $pegawai): PegawaiResource
    {
        $pegawai->update($request->validated());

        return (new PegawaiResource($pegawai->load('instansi')->loadCount('kendaraanDinas')))
            ->additional(['message' => 'Pegawai berhasil diperbarui.']);
    }

    public function destroy(Pegawai $pegawai): JsonResponse
    {
        return $this->hapus($pegawai, FilamentPegawai::DEPENDENCIES);
    }

    public function hapusMassal(IdsRequest $request): JsonResponse
    {
        return $this->hapusBanyak($request, Pegawai::class, FilamentPegawai::DEPENDENCIES, 'nama');
    }
}
