<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Instansi pemilik kendaraan. Data yang sama ditampilkan sebagai "Perangkat Daerah" (read-only).
 */
#[Table('instansi')]
#[Fillable(['nama', 'singkatan', 'alamat'])]
class Instansi extends Model
{
    public function kendaraanDinas(): HasMany
    {
        return $this->hasMany(KendaraanDinas::class);
    }
}
