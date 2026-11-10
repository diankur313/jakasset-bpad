<?php

namespace App\Filament\Widgets;

use App\Enums\KondisiKendaraan;
use App\Models\KendaraanDinas;
use Filament\Widgets\ChartWidget;

/** Komposisi kondisi kendaraan dinas (kondisi dibaca dari Master Kendaraan). */
class KondisiKendaraanChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Kondisi Kendaraan Dinas';

    protected ?string $description = 'Mengikuti kondisi di Master Data > Kendaraan.';

    protected ?string $maxHeight = '240px';

    private const WARNA = [
        'baik' => '#22c55e',
        'rusak_ringan' => '#f59e0b',
        'rusak_berat' => '#ef4444',
    ];

    /** @return array<string, int> [value enum => jumlah kendaraan dinas] */
    public static function hitung(): array
    {
        $jumlah = KendaraanDinas::query()
            ->join('master_kendaraan', 'master_kendaraan.id', '=', 'kendaraan_dinas.master_kendaraan_id')
            ->selectRaw('master_kendaraan.kondisi as nilai, count(*) as jumlah')
            ->groupBy('master_kendaraan.kondisi')
            ->pluck('jumlah', 'nilai');

        return collect(KondisiKendaraan::cases())
            ->mapWithKeys(fn (KondisiKendaraan $k) => [$k->value => (int) $jumlah->get($k->value, 0)])
            ->all();
    }

    protected function getData(): array
    {
        $data = static::hitung();

        return [
            'datasets' => [[
                'label' => 'Kendaraan Dinas',
                'data' => array_values($data),
                'backgroundColor' => array_map(fn (string $v) => self::WARNA[$v], array_keys($data)),
                'borderWidth' => 0,
            ]],
            'labels' => array_map(fn (string $v) => KondisiKendaraan::from($v)->getLabel(), array_keys($data)),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
            'cutout' => '60%',
        ];
    }
}
