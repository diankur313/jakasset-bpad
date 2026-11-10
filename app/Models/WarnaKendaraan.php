<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Master warna kendaraan, dipakai oleh Kendaraan Dinas agar penulisan warna seragam.
 */
#[Table('warna_kendaraan')]
#[Fillable(['nama', 'kode_hex'])]
class WarnaKendaraan extends Model
{
    /** Nama warna selalu Title Case dengan spasi tunggal, misalnya "abu  abu" menjadi "Abu Abu". */
    protected function nama(): Attribute
    {
        return Attribute::set(fn (string $value): string => Str::title(preg_replace('/\s+/', ' ', trim($value))));
    }

    public function kendaraanDinas(): HasMany
    {
        return $this->hasMany(KendaraanDinas::class);
    }
}
