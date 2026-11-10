<?php

namespace App\Filament\Resources\KendaraanDinas\Pages;

use App\Filament\Resources\KendaraanDinas\Actions\DownloadLabelAction;
use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKendaraanDinas extends ViewRecord
{
    protected static string $resource = KendaraanDinasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DownloadLabelAction::make()->label('Download Label'),
            EditAction::make(),
            DependencyGuard::deleteAction(KendaraanDinasResource::DEPENDENCIES),
        ];
    }
}
