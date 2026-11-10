<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Daftar warna standar. Diatur dari form Master Data > Kendaraan (field Warna Tersedia),
 * lalu dipilih di Kendaraan Dinas, agar penulisan warna seragam.
 */
#[Table('warna_kendaraan')]
#[Fillable(['nama', 'kode_hex'])]
class WarnaKendaraan extends Model
{
    /** Title Case dengan spasi tunggal, misalnya "biru  dongker" menjadi "Biru Dongker". */
    public static function rapikan(?string $nama): string
    {
        return Str::title(preg_replace('/\s+/', ' ', trim((string) $nama)));
    }

    protected function nama(): Attribute
    {
        return Attribute::set(fn (string $value): string => static::rapikan($value));
    }

    public function masterKendaraan(): BelongsToMany
    {
        return $this->belongsToMany(MasterKendaraan::class, 'master_kendaraan_warna');
    }

    public function kendaraanDinas(): HasMany
    {
        return $this->hasMany(KendaraanDinas::class);
    }
}
