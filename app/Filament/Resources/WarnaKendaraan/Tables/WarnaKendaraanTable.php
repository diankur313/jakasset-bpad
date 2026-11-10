<?php

namespace App\Filament\Resources\WarnaKendaraan\Tables;

use App\Filament\Resources\WarnaKendaraan\WarnaKendaraanResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WarnaKendaraanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('kendaraanDinas'))
            ->defaultSort('nama')
            ->columns([
                ColorColumn::make('kode_hex')->label('Contoh')->placeholder('-'),
                TextColumn::make('nama')->label('Nama Warna')->searchable()->sortable(),
                TextColumn::make('kendaraan_dinas_count')
                    ->label('Dipakai')
                    ->suffix(' unit')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DependencyGuard::deleteAction(WarnaKendaraanResource::DEPENDENCIES),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DependencyGuard::deleteBulkAction(WarnaKendaraanResource::DEPENDENCIES, 'nama'),
                ]),
            ]);
    }
}
