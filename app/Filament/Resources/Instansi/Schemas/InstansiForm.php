<?php

namespace App\Filament\Resources\Instansi\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstansiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Instansi Pemilik Kendaraan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Instansi')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->validationMessages(['unique' => 'Nama instansi ini sudah terdaftar.'])
                            ->placeholder('Dinas Pendidikan'),
                        TextInput::make('singkatan')
                            ->label('Singkatan')
                            ->maxLength(50)
                            ->placeholder('Disdik'),
                        Textarea::make('alamat')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
