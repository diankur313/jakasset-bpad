<?php

namespace App\Filament\Widgets;

use App\Models\Instansi;
use Filament\Widgets\ChartWidget;

/** Jumlah kendaraan dinas dan pegawai per perangkat daerah. */
class KendaraanPerPerangkatDaerahChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Kendaraan Dinas per Perangkat Daerah';

    protected ?string $description = 'Jumlah kendaraan dinas dan pegawai di tiap perangkat daerah.';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $instansi = Instansi::query()
            ->withCount(['kendaraanDinas', 'pegawai'])
            ->orderByDesc('kendaraan_dinas_count')
            ->orderBy('nama')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Kendaraan Dinas',
                    'data' => $instansi->pluck('kendaraan_dinas_count')->all(),
                    'backgroundColor' => '#f59e0b',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Pegawai',
                    'data' => $instansi->pluck('pegawai_count')->all(),
                    'backgroundColor' => '#3b82f6',
                    'borderRadius' => 4,
                ],
            ],
            // Pakai singkatan bila ada supaya label sumbu tidak terlalu panjang.
            'labels' => $instansi->map(fn (Instansi $i) => $i->singkatan ?: $i->nama)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
