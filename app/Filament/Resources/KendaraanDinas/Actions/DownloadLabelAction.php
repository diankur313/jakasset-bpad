<?php

namespace App\Filament\Resources\KendaraanDinas\Actions;

use App\Models\KendaraanDinas;
use App\Support\LabelAsset;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Aksi download label QR kendaraan dinas (Added Value AV-003).
 */
class DownloadLabelAction
{
    /** Download label satu kendaraan (PDF 90 x 45 mm). */
    public static function make(): Action
    {
        return Action::make('downloadLabel')
            ->label('Label')
            ->tooltip('Download label QR')
            ->icon(Heroicon::OutlinedQrCode)
            ->color('info')
            ->action(fn (KendaraanDinas $record): StreamedResponse => LabelAsset::download(collect([$record])));
    }

    /** Download label banyak kendaraan sekaligus (PDF A4, 10 label per halaman). */
    public static function bulk(): BulkAction
    {
        return BulkAction::make('downloadLabelBatch')
            ->label('Download Label')
            ->icon(Heroicon::OutlinedQrCode)
            ->color('info')
            ->action(fn (Collection $records): StreamedResponse => LabelAsset::download($records))
            ->deselectRecordsAfterCompletion();
    }
}
