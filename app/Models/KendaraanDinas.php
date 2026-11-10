<?php

namespace App\Models;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('kendaraan_dinas')]
#[Fillable(['nomor_polisi', 'master_kendaraan_id', 'warna', 'kondisi', 'status', 'instansi_id', 'pegawai_id'])]
class KendaraanDinas extends Model
{
    protected function casts(): array
    {
        return [
            'kondisi' => KondisiKendaraan::class,
            'status' => StatusKendaraan::class,
        ];
    }

    protected function nomorPolisi(): Attribute
    {
        return Attribute::set(
            fn (string $value) => strtoupper(preg_replace('/\s+/', ' ', trim($value))),
        );
    }

    public function masterKendaraan(): BelongsTo
    {
        return $this->belongsTo(MasterKendaraan::class);
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
