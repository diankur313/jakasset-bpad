<?php

namespace App\Filament\Resources\Pegawai\Tables;

use App\Filament\Resources\Pegawai\PegawaiResource;
use App\Filament\Support\DependencyGuard;
use App\Models\Pegawai;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PegawaiTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('instansi')->withCount('kendaraanDinas'))
            ->defaultSort('nama')
            ->columns([
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('nip')->label('NIP')->searchable()->copyable(),
                TextColumn::make('jabatan')->label('Jabatan')->searchable()->sortable(),
                TextColumn::make('golongan')->label('Golongan')->badge()->sortable(),
                TextColumn::make('instansi.nama')
                    ->label('Instansi')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(fn ($record): ?string => $record->instansi?->nama)
                    ->placeholder('Belum diisi'),
                TextColumn::make('kendaraan_dinas_count')
                    ->label('Kendaraan Dinas')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('instansi_id')
                    ->label('Instansi')
                    ->relationship('instansi', 'nama')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('golongan')
                    ->label('Golongan')
                    ->options(array_combine(Pegawai::GOLONGAN, Pegawai::GOLONGAN)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DependencyGuard::deleteAction(PegawaiResource::DEPENDENCIES),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DependencyGuard::deleteBulkAction(PegawaiResource::DEPENDENCIES, 'nama'),
                ]),
            ]);
    }
}
