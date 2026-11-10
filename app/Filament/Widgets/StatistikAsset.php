<?php

namespace App\Filament\Widgets;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource;
use App\Filament\Resources\MasterKendaraan\MasterKendaraanResource;
use App\Filament\Resources\Pegawai\PegawaiResource;
use App\Filament\Resources\PerangkatDaerah\PerangkatDaerahResource;
use App\Models\Instansi;
use App\Models\KendaraanDinas;
use App\Models\MasterKendaraan;
use App\Models\Pegawai;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

/** Ringkasan angka utama di Dasbor, dihitung langsung dari database. */
class StatistikAsset extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan Asset';

    protected int|array|null $columns = 3;

    /** Jumlah kendaraan dinas yang ditambahkan per bulan, 6 bulan terakhir (untuk grafik kecil). */
    public static function trenBulanan(int $bulan = 6): array
    {
        $mulai = now()->startOfMonth()->subMonths($bulan - 1);

        $perBulan = KendaraanDinas::query()
            ->where('created_at', '>=', $mulai)
            ->pluck('created_at')
            ->countBy(fn ($tanggal) => $tanggal->format('Y-m'));

        return collect(range(0, $bulan - 1))
            ->map(fn (int $i) => $perBulan->get($mulai->copy()->addMonths($i)->format('Y-m'), 0))
            ->all();
    }

    protected function getStats(): array
    {
        $totalKendaraan = KendaraanDinas::count();

        $denganMaster = fn (string $kolom, array $nilai): int => KendaraanDinas::query()
            ->whereHas('masterKendaraan', fn (Builder $q) => $q->whereIn($kolom, $nilai))
            ->count();

        $aktif = $denganMaster('status', [StatusKendaraan::Active->value]);
        $rusakRingan = $denganMaster('kondisi', [KondisiKendaraan::RusakRingan->value]);
        $rusakBerat = $denganMaster('kondisi', [KondisiKendaraan::RusakBerat->value]);
        $perluPerbaikan = $rusakRingan + $rusakBerat;

        $bulanIni = KendaraanDinas::where('created_at', '>=', now()->startOfMonth())->count();

        $totalPegawai = Pegawai::count();
        $pemegang = Pegawai::has('kendaraanDinas')->count();

        $persen = fn (int $n): string => $totalKendaraan > 0 ? round($n / $totalKendaraan * 100).'%' : '0%';

        return [
            Stat::make('Total Kendaraan Dinas', number_format($totalKendaraan, 0, ',', '.'))
                ->description("{$bulanIni} ditambahkan bulan ini")
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
                ->icon(Heroicon::OutlinedTruck)
                ->chart(static::trenBulanan())
                ->color('primary')
                ->url(KendaraanDinasResource::getUrl()),

            Stat::make('Kendaraan Active', number_format($aktif, 0, ',', '.'))
                ->description($persen($aktif).' dari seluruh kendaraan dinas')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->icon(Heroicon::OutlinedBolt)
                ->color('success'),

            Stat::make('Perlu Perbaikan', number_format($perluPerbaikan, 0, ',', '.'))
                ->description("Rusak ringan {$rusakRingan} · Rusak berat {$rusakBerat}")
                ->descriptionIcon(Heroicon::OutlinedWrenchScrewdriver)
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color($perluPerbaikan > 0 ? 'warning' : 'success'),

            Stat::make('Pegawai', number_format($totalPegawai, 0, ',', '.'))
                ->description("{$pemegang} menjadi pemegang kendaraan")
                ->descriptionIcon(Heroicon::OutlinedKey)
                ->icon(Heroicon::OutlinedUsers)
                ->color('info')
                ->url(PegawaiResource::getUrl()),

            Stat::make('Perangkat Daerah', number_format(Instansi::count(), 0, ',', '.'))
                ->description(Instansi::has('kendaraanDinas')->count().' memiliki kendaraan dinas')
                ->descriptionIcon(Heroicon::OutlinedBuildingOffice)
                ->icon(Heroicon::OutlinedBuildingLibrary)
                ->color('gray')
                ->url(PerangkatDaerahResource::getUrl()),

            Stat::make('Master Kendaraan', number_format(MasterKendaraan::count(), 0, ',', '.'))
                ->description(MasterKendaraan::distinct()->count('merek').' merek terdaftar')
                ->descriptionIcon(Heroicon::OutlinedTag)
                ->icon(Heroicon::OutlinedRectangleStack)
                ->color('gray')
                ->url(MasterKendaraanResource::getUrl()),
        ];
    }
}
