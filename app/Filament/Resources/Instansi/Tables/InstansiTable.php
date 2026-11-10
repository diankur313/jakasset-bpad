<?php

namespace App\Filament\Resources\Instansi\Tables;

use App\Filament\Resources\Instansi\InstansiResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstansiTable
{
    /** Kolom yang dipakai bersama dengan tabel Perangkat Daerah. */
    public static function columns(): array
    {
        return [
            TextColumn::make('nama')->label('Nama Instansi')->searchable()->sortable(),
            TextColumn::make('singkatan')->label('Singkatan')->searchable()->placeholder('-'),
            TextColumn::make('alamat')->label('Alamat')->limit(50)->placeholder('-')->toggleable(),
            TextColumn::make('kendaraan_dinas_count')
                ->label('Kendaraan Dinas')
                ->suffix(' unit')
                ->sortable()
                ->alignCenter(),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('kendaraanDinas'))
            ->defaultSort('nama')
            ->columns(static::columns())
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DependencyGuard::deleteAction(InstansiResource::DEPENDENCIES),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DependencyGuard::deleteBulkAction(InstansiResource::DEPENDENCIES, 'nama'),
                ]),
            ]);
    }
}
