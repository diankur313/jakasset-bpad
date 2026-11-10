<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\WarnaKendaraanRequest;
use App\Http\Resources\WarnaKendaraanResource;
use App\Models\MasterKendaraan;
use App\Models\WarnaKendaraan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Daftar warna standar. Di panel admin tidak punya menu sendiri (diatur dari field Warna Tersedia
 * di Master Data > Kendaraan), jadi API-nya hanya daftar & tambah warna baru.
 */
class WarnaKendaraanController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // ?master_kendaraan_id=ID → hanya Warna Tersedia master tsb (pilihan warna untuk Kendaraan Dinas).
        if ($request->filled('master_kendaraan_id')) {
            $master = MasterKendaraan::findOrFail($request->integer('master_kendaraan_id'));

            return WarnaKendaraanResource::collection($master->warna);
        }

        $query = WarnaKendaraan::query();
        $this->cari($query, $request, ['nama']);
        $this->urutkan($query, $request, ['nama', 'created_at'], 'nama');

        return WarnaKendaraanResource::collection($query->paginate($this->perHalaman($request))->withQueryString());
    }

    public function store(WarnaKendaraanRequest $request): JsonResponse
    {
        $warna = WarnaKendaraan::create($request->validated());

        return (new WarnaKendaraanResource($warna))
            ->additional(['message' => 'Warna berhasil ditambahkan.'])
            ->response()->setStatusCode(201);
    }
}
