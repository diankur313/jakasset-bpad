<?php

namespace App\Filament\Resources\Instansi\Pages;

use App\Filament\Resources\Instansi\InstansiResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInstansi extends ViewRecord
{
    protected static string $resource = InstansiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DependencyGuard::deleteAction(InstansiResource::DEPENDENCIES),
        ];
    }
}
