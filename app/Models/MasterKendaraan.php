<?php

namespace App\Models;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('master_kendaraan')]
#[Fillable(['merek', 'tipe_model', 'tahun_produksi', 'kondisi', 'status'])]
class MasterKendaraan extends Model
{
    protected function casts(): array
    {
        return [
            'tahun_produksi' => 'integer',
            'kondisi' => KondisiKendaraan::class,
            'status' => StatusKendaraan::class,
        ];
    }

    public function kendaraanDinas(): HasMany
    {
        return $this->hasMany(KendaraanDinas::class);
    }

    /** Warna yang tersedia untuk tipe kendaraan ini. */
    public function warna(): BelongsToMany
    {
        return $this->belongsToMany(WarnaKendaraan::class, 'master_kendaraan_warna')->orderBy('nama');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->merek} {$this->tipe_model} ({$this->tahun_produksi})";
    }
}
