<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource as FilamentKendaraan;
use App\Http\Requests\Api\IdsRequest;
use App\Http\Requests\Api\KendaraanDinasRequest;
use App\Http\Resources\KendaraanDinasResource;
use App\Models\KendaraanDinas;
use App\Support\LabelAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Asset > Kendaraan Dinas (CRUD) dan download label QR (AV-003). */
class KendaraanDinasController extends ApiController
{
    private const RELASI = ['masterKendaraan', 'warnaKendaraan', 'instansi', 'pegawai'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = KendaraanDinas::query()->with(self::RELASI);
        $this->cari($query, $request, ['nomor_polisi', 'nomor_asset']);

        $query->when($request->filled('nomor_asset'), fn ($q) => $q->where('nomor_asset', $request->query('nomor_asset')));

        foreach (['master_kendaraan_id', 'warna_kendaraan_id', 'instansi_id', 'pegawai_id'] as $filter) {
            $query->when($request->filled($filter), fn ($q) => $q->where($filter, $request->integer($filter)));
        }

        // Kondisi, status, dan merek ada di Master Kendaraan.
        foreach (['kondisi', 'status', 'merek'] as $filter) {
            $query->when($request->filled($filter), fn ($q) => $q->whereHas(
                'masterKendaraan',
                fn (Builder $m) => $m->where($filter, $request->query($filter)),
            ));
        }

        $this->urutkan($query, $request, ['nomor_asset', 'nomor_polisi', 'created_at'], '-created_at');

        return KendaraanDinasResource::collection($query->paginate($this->perHalaman($request))->withQueryString());
    }

    public function store(KendaraanDinasRequest $request): JsonResponse
    {
        // Nomor Asset dibuat otomatis oleh KendaraanDinas::creating.
        $kendaraan = KendaraanDinas::create($request->validated());

        return (new KendaraanDinasResource($kendaraan->load(self::RELASI)))
            ->additional(['message' => 'Kendaraan dinas berhasil ditambahkan.'])
            ->response()->setStatusCode(201);
    }

    public function show(KendaraanDinas $kendaraanDinas): KendaraanDinasResource
    {
        return new KendaraanDinasResource($kendaraanDinas->load(self::RELASI));
    }

    public function update(KendaraanDinasRequest $request, KendaraanDinas $kendaraanDinas): KendaraanDinasResource
    {
        $kendaraanDinas->update($request->validated());

        return (new KendaraanDinasResource($kendaraanDinas->load(self::RELASI)))
            ->additional(['message' => 'Kendaraan dinas berhasil diperbarui.']);
    }

    public function destroy(KendaraanDinas $kendaraanDinas): JsonResponse
    {
        return $this->hapus($kendaraanDinas, FilamentKendaraan::DEPENDENCIES);
    }

    public function hapusMassal(IdsRequest $request): JsonResponse
    {
        return $this->hapusBanyak($request, KendaraanDinas::class, FilamentKendaraan::DEPENDENCIES, 'nomor_polisi');
    }

    /** PDF label satu kendaraan (90 x 45 mm). */
    public function label(KendaraanDinas $kendaraanDinas): StreamedResponse
    {
        return LabelAsset::download(collect([$kendaraanDinas]));
    }

    /** PDF label banyak kendaraan (A4, 10 label per halaman). */
    public function labelBatch(IdsRequest $request): StreamedResponse|JsonResponse
    {
        $kendaraan = KendaraanDinas::query()->whereKey($request->validated('ids'))->orderBy('nomor_asset')->get();

        if ($kendaraan->isEmpty()) {
            return response()->json(['message' => 'Tidak ada kendaraan dinas yang ditemukan dari daftar ID.'], 404);
        }

        return LabelAsset::download($kendaraan);
    }
}
