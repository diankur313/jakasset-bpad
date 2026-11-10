<?php

namespace App\Http\Controllers\Api;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Http\Controllers\Controller;
use App\Http\Resources\EnumResource;
use App\Models\Pegawai;
use Illuminate\Http\JsonResponse;

/** Daftar pilihan tetap (enum) untuk mengisi dropdown di aplikasi klien. */
class ReferensiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'kondisi' => EnumResource::pilihan(KondisiKendaraan::class),
                'status' => EnumResource::pilihan(StatusKendaraan::class),
                'golongan' => Pegawai::GOLONGAN,
            ],
        ]);
    }
}
