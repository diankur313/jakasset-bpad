<?php

namespace App\Filament\Resources\WarnaKendaraan\Pages;

use App\Filament\Resources\WarnaKendaraan\WarnaKendaraanResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWarnaKendaraan extends EditRecord
{
    protected static string $resource = WarnaKendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DependencyGuard::deleteAction(WarnaKendaraanResource::DEPENDENCIES),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
