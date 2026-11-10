<?php

namespace App\Http\Controllers\Api;

use App\Filament\Support\DependencyGuard;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IdsRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Helper bersama controller REST API: paginasi, pengurutan, pencarian,
 * dan penghapusan yang mengecek dependensi (aturan yang sama dengan DependencyGuard di panel admin).
 */
abstract class ApiController extends Controller
{
    protected const PER_HALAMAN_DEFAULT = 15;

    protected const PER_HALAMAN_MAKS = 100;

    protected function perHalaman(Request $request): int
    {
        return max(1, min($request->integer('per_page', self::PER_HALAMAN_DEFAULT), self::PER_HALAMAN_MAKS));
    }

    /**
     * ?sort=kolom (naik) atau ?sort=-kolom (turun). Kolom di luar daftar diabaikan.
     *
     * @param  array<int, string>  $kolom
     */
    protected function urutkan(Builder $query, Request $request, array $kolom, string $default): Builder
    {
        $sort = (string) $request->query('sort', $default);
        $arah = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $nama = ltrim($sort, '-');

        if (! in_array($nama, $kolom, true)) {
            $arah = str_starts_with($default, '-') ? 'desc' : 'asc';
            $nama = ltrim($default, '-');
        }

        return $query->orderBy($nama, $arah)->orderBy('id');
    }

    /** ?search=kata dicari (LIKE) di beberapa kolom. */
    protected function cari(Builder $query, Request $request, array $kolom): Builder
    {
        $kata = trim((string) $request->query('search', ''));

        if ($kata === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($kolom, $kata): void {
            foreach ($kolom as $k) {
                $q->orWhere($k, 'like', "%{$kata}%");
            }
        });
    }

    /** @return array<int, array{data: string, jumlah: int, contoh: array<int, string>}> */
    protected function dependensi(Model $record, array $relasi): array
    {
        return array_map(fn (array $dep): array => [
            'data' => $dep['label'],
            'jumlah' => $dep['count'],
            'contoh' => $dep['samples'],
        ], DependencyGuard::dependents($record, $relasi));
    }

    /** Hapus satu data. Ditolak (409) bila masih dipakai data lain. */
    protected function hapus(Model $record, array $relasi): JsonResponse
    {
        $dependensi = $this->dependensi($record, $relasi);

        if ($dependensi !== []) {
            return response()->json([
                'message' => 'Data tidak dapat dihapus karena masih digunakan oleh data lain. Hapus atau pindahkan data tersebut terlebih dahulu.',
                'dependensi' => $dependensi,
            ], 409);
        }

        $record->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    /**
     * Hapus banyak data sekaligus. Data yang masih dipakai dilewati (tidak dihapus).
     *
     * @param  class-string<Model>  $model
     */
    protected function hapusBanyak(IdsRequest $request, string $model, array $relasi, string $kolomJudul): JsonResponse
    {
        $ids = array_map('intval', $request->validated('ids'));
        $records = $model::query()->whereKey($ids)->get();

        $dihapus = [];
        $dilewati = [];

        foreach ($records as $record) {
            $dependensi = $this->dependensi($record, $relasi);

            if ($dependensi === []) {
                $record->delete();
                $dihapus[] = $record->getKey();
            } else {
                $dilewati[] = ['id' => $record->getKey(), 'nama' => $record->{$kolomJudul}, 'dependensi' => $dependensi];
            }
        }

        $ditemukan = $records->modelKeys();

        return response()->json([
            'message' => count($dihapus).' data dihapus, '.count($dilewati).' data dilewati.',
            'dihapus' => $dihapus,
            'dilewati' => $dilewati,
            'tidak_ditemukan' => array_values(array_diff($ids, $ditemukan)),
        ]);
    }
}
