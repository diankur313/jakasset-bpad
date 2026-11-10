<?php

namespace App\Filament\Resources\KendaraanDinas\Tables;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource;
use App\Filament\Support\DependencyGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KendaraanDinasTable
{
    /** Kolom yang dipakai bersama dengan relation manager Perangkat Daerah. */
    public static function columns(bool $withInstansi = true): array
    {
        return array_values(array_filter([
            TextColumn::make('nomor_polisi')->label('Nomor Polisi')->searchable()->sortable()->weight('bold'),
            TextColumn::make('masterKendaraan.merek')->label('Merek')->searchable()->sortable(),
            TextColumn::make('masterKendaraan.tipe_model')->label('Tipe / Model')->searchable(),
            TextColumn::make('masterKendaraan.tahun_produksi')->label('Tahun')->sortable(),
            TextColumn::make('warna')->label('Warna')->toggleable(),
            TextColumn::make('kondisi')->label('Kondisi')->badge()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->sortable(),
            $withInstansi ? TextColumn::make('instansi.nama')->label('Perangkat Daerah')->searchable()->sortable()->wrap() : null,
            TextColumn::make('pegawai.nama')->label('Pemegang')->searchable()->placeholder('-')->toggleable(),
        ]));
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['masterKendaraan', 'instansi', 'pegawai']))
            ->defaultSort('created_at', 'desc')
            ->columns(static::columns())
            ->filters([
                SelectFilter::make('instansi_id')
                    ->label('Perangkat Daerah')
                    ->relationship('instansi', 'nama')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('kondisi')->label('Kondisi')->options(KondisiKendaraan::class),
                SelectFilter::make('status')->label('Status')->options(StatusKendaraan::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DependencyGuard::deleteAction(KendaraanDinasResource::DEPENDENCIES),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DependencyGuard::deleteBulkAction(KendaraanDinasResource::DEPENDENCIES, 'nomor_polisi'),
                ]),
            ]);
    }
}
