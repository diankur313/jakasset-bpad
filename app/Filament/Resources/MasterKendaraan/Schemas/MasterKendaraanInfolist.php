<?php

namespace App\Filament\Resources\MasterKendaraan\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterKendaraanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Master Kendaraan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('merek')->label('Merek'),
                        TextEntry::make('tipe_model')->label('Tipe / Model'),
                        TextEntry::make('tahun_produksi')->label('Tahun Produksi'),
                        TextEntry::make('kondisi')->label('Kondisi')->badge(),
                        TextEntry::make('status')->label('Status')->badge(),
                        TextEntry::make('kendaraanDinas.nomor_polisi')
                            ->label('Dipakai oleh Kendaraan Dinas')
                            ->badge()
                            ->placeholder('Belum dipakai')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
