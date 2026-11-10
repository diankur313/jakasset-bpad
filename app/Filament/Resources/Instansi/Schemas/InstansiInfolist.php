<?php

namespace App\Filament\Resources\Instansi\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Dipakai juga oleh PerangkatDaerahResource.
 */
class InstansiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Instansi')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('nama')->label('Nama Instansi'),
                        TextEntry::make('singkatan')->label('Singkatan')->placeholder('-'),
                        TextEntry::make('alamat')->label('Alamat')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('kendaraan_dinas_count')
                            ->label('Jumlah Kendaraan Dinas')
                            ->state(fn ($record): int => $record->kendaraanDinas()->count())
                            ->suffix(' unit'),
                        TextEntry::make('pegawai_count')
                            ->label('Jumlah Pegawai')
                            ->state(fn ($record): int => $record->pegawai()->count())
                            ->suffix(' orang'),
                    ]),
            ]);
    }
}
