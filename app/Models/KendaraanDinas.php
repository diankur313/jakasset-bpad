<?php

namespace App\Models;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Support\NomorAsset;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('kendaraan_dinas')]
#[Fillable(['nomor_polisi', 'master_kendaraan_id', 'warna', 'kondisi', 'status', 'instansi_id', 'pegawai_id'])]
class KendaraanDinas extends Model
{
    /** Jenis asset yang dipakai di nomor asset: YYYY-mm-dd/KENDARAAN/0001. */
    public const JENIS_ASSET = 'KENDARAAN';

    protected static function booted(): void
    {
        // Nomor asset dibuat otomatis saat data pertama kali disimpan dan tidak bisa diubah.
        static::creating(function (KendaraanDinas $kendaraan): void {
            if (blank($kendaraan->nomor_asset)) {
                $kendaraan->nomor_asset = NomorAsset::generate(self::JENIS_ASSET);
            }
        });

        static::updating(function (KendaraanDinas $kendaraan): void {
            if ($kendaraan->isDirty('nomor_asset') && filled($kendaraan->getOriginal('nomor_asset'))) {
                $kendaraan->nomor_asset = $kendaraan->getOriginal('nomor_asset');
            }
        });
    }

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
