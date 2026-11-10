<?php

namespace App\Filament\Resources\WarnaKendaraan\Schemas;

use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarnaKendaraanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Warna Kendaraan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('nama')->label('Nama Warna'),
                        ColorEntry::make('kode_hex')->label('Contoh Warna')->placeholder('-'),
                        TextEntry::make('kendaraanDinas.nomor_polisi')
                            ->label('Dipakai oleh Kendaraan Dinas')
                            ->badge()
                            ->placeholder('Belum dipakai')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
