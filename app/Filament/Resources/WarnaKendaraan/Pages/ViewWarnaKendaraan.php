<?php

namespace App\Filament\Resources\WarnaKendaraan\Pages;

use App\Filament\Resources\WarnaKendaraan\WarnaKendaraanResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWarnaKendaraan extends ViewRecord
{
    protected static string $resource = WarnaKendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DependencyGuard::deleteAction(WarnaKendaraanResource::DEPENDENCIES),
        ];
    }
}
