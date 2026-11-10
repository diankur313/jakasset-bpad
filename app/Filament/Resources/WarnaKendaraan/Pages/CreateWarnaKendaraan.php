<?php

namespace App\Filament\Resources\WarnaKendaraan\Pages;

use App\Filament\Resources\WarnaKendaraan\WarnaKendaraanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarnaKendaraan extends CreateRecord
{
    protected static string $resource = WarnaKendaraanResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
