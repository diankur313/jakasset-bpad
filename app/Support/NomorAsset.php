<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Generator nomor asset dengan format: YYYY-mm-dd/JENIS ASSET/0001
 *
 * - Tanggal = tanggal pencatatan asset (timezone aplikasi, Asia/Jakarta).
 * - Running number 4 digit dihitung per jenis asset per tanggal, mulai dari 0001 setiap hari.
 * - Nomor yang sudah terpakai tidak pernah dipakai ulang, walaupun asetnya dihapus.
 * - Penghitung disimpan di tabel `nomor_asset_sequences`. Kenaikan nomor memakai
 *   UPDATE ... SET nomor_terakhir = nomor_terakhir + 1, yang atomik di SQLite maupun MySQL/PostgreSQL,
 *   jadi dua penyimpanan bersamaan tidak akan mendapat nomor yang sama.
 */
class NomorAsset
{
    public const MAX_RUNNING_NUMBER = 9999;

    public static function generate(string $jenisAsset, ?CarbonInterface $tanggal = null): string
    {
        $tanggal = ($tanggal ?? now())->copy()->setTimezone(config('app.timezone'))->toDateString();

        $nomor = DB::transaction(function () use ($jenisAsset, $tanggal): int {
            $now = now();

            DB::table('nomor_asset_sequences')->insertOrIgnore([
                'jenis_asset' => $jenisAsset,
                'tanggal' => $tanggal,
                'nomor_terakhir' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $query = DB::table('nomor_asset_sequences')
                ->where('jenis_asset', $jenisAsset)
                ->where('tanggal', $tanggal);

            (clone $query)->update([
                'nomor_terakhir' => DB::raw('nomor_terakhir + 1'),
                'updated_at' => $now,
            ]);

            return (int) (clone $query)->value('nomor_terakhir');
        });

        if ($nomor > self::MAX_RUNNING_NUMBER) {
            throw new \OverflowException("Running number nomor asset {$jenisAsset} untuk tanggal {$tanggal} sudah melewati ".self::MAX_RUNNING_NUMBER.'.');
        }

        return sprintf('%s/%s/%04d', $tanggal, $jenisAsset, $nomor);
    }
}
