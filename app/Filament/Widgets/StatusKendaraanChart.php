<?php

namespace App\Filament\Widgets;

use App\Enums\StatusKendaraan;
use App\Models\KendaraanDinas;
use Filament\Widgets\ChartWidget;

/** Komposisi status kendaraan dinas (status dibaca dari Master Kendaraan). */
class StatusKendaraanChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Status Kendaraan Dinas';

    protected ?string $description = 'Mengikuti status di Master Data > Kendaraan.';

    protected ?string $maxHeight = '240px';

    private const WARNA = [
        'active' => '#22c55e',
        'inactive' => '#9ca3af',
        'standby' => '#3b82f6',
    ];

    /** @return array<string, int> [value enum => jumlah kendaraan dinas] */
    public static function hitung(): array
    {
        $jumlah = KendaraanDinas::query()
            ->join('master_kendaraan', 'master_kendaraan.id', '=', 'kendaraan_dinas.master_kendaraan_id')
            ->selectRaw('master_kendaraan.status as nilai, count(*) as jumlah')
            ->groupBy('master_kendaraan.status')
            ->pluck('jumlah', 'nilai');

        return collect(StatusKendaraan::cases())
            ->mapWithKeys(fn (StatusKendaraan $k) => [$k->value => (int) $jumlah->get($k->value, 0)])
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
            'labels' => array_map(fn (string $v) => StatusKendaraan::from($v)->getLabel(), array_keys($data)),
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
