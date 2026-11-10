<?php

namespace App\Filament\Resources\MasterKendaraan\Pages;

use App\Filament\Resources\MasterKendaraan\MasterKendaraanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterKendaraan extends CreateRecord
{
    protected static string $resource = MasterKendaraanResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
