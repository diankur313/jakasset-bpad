<?php

namespace App\Filament\Resources\PerangkatDaerah\RelationManagers;

use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource;
use App\Filament\Resources\KendaraanDinas\Tables\KendaraanDinasTable;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

/**
 * Daftar kendaraan dinas milik perangkat daerah (read-only).
 */
class KendaraanDinasRelationManager extends RelationManager
{
    protected static string $relationship = 'kendaraanDinas';

    protected static ?string $title = 'Kendaraan Dinas';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_polisi')
            ->modifyQueryUsing(fn ($query) => $query->with(['masterKendaraan', 'warnaKendaraan', 'pegawai']))
            ->columns(KendaraanDinasTable::columns(withInstansi: false))
            ->recordActions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn ($record): string => KendaraanDinasResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
