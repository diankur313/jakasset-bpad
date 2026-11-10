<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('pegawai')]
#[Fillable(['nama', 'nip', 'jabatan', 'golongan', 'instansi_id'])]
class Pegawai extends Model
{
    /** Golongan ruang PNS. */
    public const GOLONGAN = [
        'I/a', 'I/b', 'I/c', 'I/d',
        'II/a', 'II/b', 'II/c', 'II/d',
        'III/a', 'III/b', 'III/c', 'III/d',
        'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e',
    ];

    /** Instansi (perangkat daerah) tempat pegawai bertugas. */
    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    public function kendaraanDinas(): HasMany
    {
        return $this->hasMany(KendaraanDinas::class);
    }
}
