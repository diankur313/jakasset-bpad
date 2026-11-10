<?php

namespace App\Filament\Resources\KendaraanDinas\Pages;

use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKendaraanDinas extends ListRecords
{
    protected static string $resource = KendaraanDinasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
