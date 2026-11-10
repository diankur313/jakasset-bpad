<?php

namespace App\Filament\Resources\MasterKendaraan\Pages;

use App\Filament\Resources\MasterKendaraan\MasterKendaraanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterKendaraan extends ListRecords
{
    protected static string $resource = MasterKendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
