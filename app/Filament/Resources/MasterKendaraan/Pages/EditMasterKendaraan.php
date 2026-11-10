<?php

namespace App\Filament\Resources\MasterKendaraan\Pages;

use App\Filament\Resources\MasterKendaraan\MasterKendaraanResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterKendaraan extends EditRecord
{
    protected static string $resource = MasterKendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DependencyGuard::deleteAction(MasterKendaraanResource::DEPENDENCIES),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
