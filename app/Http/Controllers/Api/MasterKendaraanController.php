<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\MasterKendaraan\MasterKendaraanResource as FilamentMaster;
use App\Http\Requests\Api\IdsRequest;
use App\Http\Requests\Api\MasterKendaraanRequest;
use App\Http\Resources\MasterKendaraanResource;
use App\Models\MasterKendaraan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/** Master Data > Kendaraan (CRUD), termasuk Warna Tersedia. */
class MasterKendaraanController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MasterKendaraan::query()->with('warna')->withCount('kendaraanDinas');
        $this->cari($query, $request, ['merek', 'tipe_model']);

        foreach (['merek', 'tipe_model', 'tahun_produksi', 'kondisi', 'status'] as $filter) {
            $query->when($request->filled($filter), fn ($q) => $q->where($filter, $request->query($filter)));
        }

        $this->urutkan($query, $request, ['merek', 'tipe_model', 'tahun_produksi', 'created_at'], 'merek');

        return MasterKendaraanResource::collection($query->paginate($this->perHalaman($request))->withQueryString());
    }

    public function store(MasterKendaraanRequest $request): JsonResponse
    {
        $master = DB::transaction(function () use ($request): MasterKendaraan {
            $master = MasterKendaraan::create(Arr::except($request->validated(), 'warna'));
            $master->warna()->sync($request->validated('warna'));

            return $master;
        });

        return (new MasterKendaraanResource($master->load('warna')->loadCount('kendaraanDinas')))
            ->additional(['message' => 'Master kendaraan berhasil ditambahkan.'])
            ->response()->setStatusCode(201);
    }

    public function show(MasterKendaraan $masterKendaraan): MasterKendaraanResource
    {
        return new MasterKendaraanResource($masterKendaraan->load('warna')->loadCount('kendaraanDinas'));
    }

    public function update(MasterKendaraanRequest $request, MasterKendaraan $masterKendaraan): MasterKendaraanResource
    {
        DB::transaction(function () use ($request, $masterKendaraan): void {
            $masterKendaraan->update(Arr::except($request->validated(), 'warna'));
            $masterKendaraan->warna()->sync($request->validated('warna'));
        });

        return (new MasterKendaraanResource($masterKendaraan->load('warna')->loadCount('kendaraanDinas')))
            ->additional(['message' => 'Master kendaraan berhasil diperbarui.']);
    }

    public function destroy(MasterKendaraan $masterKendaraan): JsonResponse
    {
        return $this->hapus($masterKendaraan, FilamentMaster::DEPENDENCIES);
    }

    public function hapusMassal(IdsRequest $request): JsonResponse
    {
        return $this->hapusBanyak($request, MasterKendaraan::class, FilamentMaster::DEPENDENCIES, 'label');
    }
}
