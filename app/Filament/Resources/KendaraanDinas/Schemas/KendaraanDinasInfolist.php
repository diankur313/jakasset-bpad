<?php

namespace App\Filament\Resources\KendaraanDinas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KendaraanDinasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Kendaraan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('nomor_asset')->label('Nomor Asset')->weight('bold')->copyable(),
                        TextEntry::make('nomor_polisi')->label('Nomor Polisi')->weight('bold'),
                        TextEntry::make('masterKendaraan.merek')->label('Merek'),
                        TextEntry::make('masterKendaraan.tipe_model')->label('Tipe / Model'),
                        TextEntry::make('masterKendaraan.tahun_produksi')->label('Tahun Produksi'),
                        TextEntry::make('warnaKendaraan.nama')->label('Warna Kendaraan')->placeholder('-'),
                        TextEntry::make('kondisi')->label('Kondisi')->badge(),
                        TextEntry::make('status')->label('Status')->badge(),
                    ]),
                Section::make('Kepemilikan & Pemegang')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('instansi.nama')->label('Perangkat Daerah (Pemilik)'),
                        TextEntry::make('pegawai.nama')->label('Pemegang (Pegawai)')->placeholder('-'),
                        TextEntry::make('pegawai.nip')->label('NIP Pemegang')->placeholder('-'),
                        TextEntry::make('pegawai.jabatan')->label('Jabatan Pemegang')->placeholder('-'),
                    ]),
            ]);
    }
}
