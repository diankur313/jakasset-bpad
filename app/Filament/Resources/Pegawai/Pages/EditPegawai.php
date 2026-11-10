<?php

namespace App\Filament\Resources\Pegawai\Pages;

use App\Filament\Resources\Pegawai\PegawaiResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPegawai extends EditRecord
{
    protected static string $resource = PegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DependencyGuard::deleteAction(PegawaiResource::DEPENDENCIES),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
