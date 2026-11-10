<?php

namespace App\Filament\Resources\PerangkatDaerah\RelationManagers;

use App\Filament\Resources\Pegawai\PegawaiResource;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Daftar pegawai perangkat daerah (read-only).
 */
class PegawaiRelationManager extends RelationManager
{
    protected static string $relationship = 'pegawai';

    protected static ?string $title = 'Pegawai';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->modifyQueryUsing(fn ($query) => $query->withCount('kendaraanDinas'))
            ->defaultSort('nama')
            ->columns([
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('nip')->label('NIP')->searchable(),
                TextColumn::make('jabatan')->label('Jabatan')->searchable(),
                TextColumn::make('golongan')->label('Golongan')->badge(),
                TextColumn::make('kendaraan_dinas_count')->label('Kendaraan Dinas')->alignCenter(),
            ])
            ->recordActions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn ($record): string => PegawaiResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
