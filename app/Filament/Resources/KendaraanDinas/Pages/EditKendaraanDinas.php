<?php

namespace App\Filament\Resources\KendaraanDinas\Pages;

use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditKendaraanDinas extends EditRecord
{
    protected static string $resource = KendaraanDinasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DependencyGuard::deleteAction(KendaraanDinasResource::DEPENDENCIES),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
