<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Pegawai */
class PegawaiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'nip' => $this->nip,
            'jabatan' => $this->jabatan,
            'golongan' => $this->golongan,
            'instansi_id' => $this->instansi_id,
            'instansi' => $this->whenLoaded('instansi', fn () => $this->instansi ? [
                'id' => $this->instansi->id,
                'nama' => $this->instansi->nama,
                'singkatan' => $this->instansi->singkatan,
            ] : null),
            'jumlah_kendaraan_dinas' => $this->whenCounted('kendaraanDinas'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
