<?php

namespace App\Filament\Resources\MasterKendaraan\Pages;

use App\Filament\Resources\MasterKendaraan\MasterKendaraanResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMasterKendaraan extends ViewRecord
{
    protected static string $resource = MasterKendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DependencyGuard::deleteAction(MasterKendaraanResource::DEPENDENCIES),
        ];
    }
}
