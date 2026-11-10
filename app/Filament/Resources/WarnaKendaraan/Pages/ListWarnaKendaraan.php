<?php

namespace App\Filament\Resources\WarnaKendaraan\Pages;

use App\Filament\Resources\WarnaKendaraan\WarnaKendaraanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarnaKendaraan extends ListRecords
{
    protected static string $resource = WarnaKendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
