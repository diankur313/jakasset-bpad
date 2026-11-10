<?php

namespace App\Filament\Resources\KendaraanDinas\Pages;

use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKendaraanDinas extends CreateRecord
{
    protected static string $resource = KendaraanDinasResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
