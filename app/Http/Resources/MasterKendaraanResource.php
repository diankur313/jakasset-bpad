<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MasterKendaraan */
class MasterKendaraanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'merek' => $this->merek,
            'tipe_model' => $this->tipe_model,
            'tahun_produksi' => $this->tahun_produksi,
            'kondisi' => EnumResource::make($this->kondisi),
            'status' => EnumResource::make($this->status),
            'warna' => WarnaKendaraanResource::collection($this->whenLoaded('warna')),
            'jumlah_kendaraan_dinas' => $this->whenCounted('kendaraanDinas'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
