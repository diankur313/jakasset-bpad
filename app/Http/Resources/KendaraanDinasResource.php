<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Kondisi & Status dibaca dari Master Kendaraan (tidak disimpan di kendaraan dinas).
 *
 * @mixin \App\Models\KendaraanDinas
 */
class KendaraanDinasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $master = $this->masterKendaraan;

        return [
            'id' => $this->id,
            'nomor_asset' => $this->nomor_asset,
            'nomor_polisi' => $this->nomor_polisi,
            'master_kendaraan_id' => $this->master_kendaraan_id,
            'merek' => $master?->merek,
            'tipe_model' => $master?->tipe_model,
            'tahun_produksi' => $master?->tahun_produksi,
            'kondisi' => EnumResource::make($master?->kondisi),
            'status' => EnumResource::make($master?->status),
            'warna_kendaraan_id' => $this->warna_kendaraan_id,
            'warna' => $this->warnaKendaraan ? new WarnaKendaraanResource($this->warnaKendaraan) : null,
            'instansi_id' => $this->instansi_id,
            'instansi' => $this->instansi ? [
                'id' => $this->instansi->id,
                'nama' => $this->instansi->nama,
                'singkatan' => $this->instansi->singkatan,
            ] : null,
            'pegawai_id' => $this->pegawai_id,
            'pemegang' => $this->pegawai ? [
                'id' => $this->pegawai->id,
                'nama' => $this->pegawai->nama,
                'nip' => $this->pegawai->nip,
            ] : null,
            'url_label' => route('api.kendaraan-dinas.label', $this->resource),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
