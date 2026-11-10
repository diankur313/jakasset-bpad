<?php

namespace App\Filament\Resources\Instansi\Pages;

use App\Filament\Resources\Instansi\InstansiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInstansi extends CreateRecord
{
    protected static string $resource = InstansiResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
