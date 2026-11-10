<?php

namespace App\Filament\Resources\MasterKendaraan\Tables;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Filament\Resources\MasterKendaraan\MasterKendaraanResource;
use App\Filament\Support\DependencyGuard;
use App\Models\MasterKendaraan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MasterKendaraanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('kendaraanDinas'))
            ->defaultSort('merek')
            ->columns([
                TextColumn::make('merek')->label('Merek')->searchable()->sortable(),
                TextColumn::make('tipe_model')->label('Tipe / Model')->searchable()->sortable(),
                TextColumn::make('tahun_produksi')->label('Tahun Produksi')->sortable(),
                TextColumn::make('kondisi')->label('Kondisi')->badge()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable(),
                TextColumn::make('kendaraan_dinas_count')
                    ->label('Dipakai')
                    ->suffix(' unit')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('merek')
                    ->label('Merek')
                    ->options(fn (): array => MasterKendaraan::query()->distinct()->orderBy('merek')->pluck('merek', 'merek')->all()),
                SelectFilter::make('kondisi')->label('Kondisi')->options(KondisiKendaraan::class),
                SelectFilter::make('status')->label('Status')->options(StatusKendaraan::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DependencyGuard::deleteAction(MasterKendaraanResource::DEPENDENCIES),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DependencyGuard::deleteBulkAction(MasterKendaraanResource::DEPENDENCIES, 'label'),
                ]),
            ]);
    }
}
