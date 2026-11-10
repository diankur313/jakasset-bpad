<?php

namespace App\Filament\Resources\Pegawai\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PegawaiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pegawai')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('nama')->label('Nama'),
                        TextEntry::make('nip')->label('NIP')->copyable(),
                        TextEntry::make('jabatan')->label('Jabatan'),
                        TextEntry::make('golongan')->label('Golongan')->badge(),
                        TextEntry::make('instansi.nama')->label('Instansi (Perangkat Daerah)')->placeholder('Belum diisi')->columnSpanFull(),
                        TextEntry::make('kendaraanDinas.nomor_polisi')
                            ->label('Kendaraan Dinas yang Dipegang')
                            ->badge()
                            ->placeholder('Tidak ada')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
