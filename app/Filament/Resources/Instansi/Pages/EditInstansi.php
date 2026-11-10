<?php

namespace App\Filament\Resources\Instansi\Pages;

use App\Filament\Resources\Instansi\InstansiResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInstansi extends EditRecord
{
    protected static string $resource = InstansiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DependencyGuard::deleteAction(InstansiResource::DEPENDENCIES),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
