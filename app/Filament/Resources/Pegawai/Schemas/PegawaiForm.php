<?php

namespace App\Filament\Resources\Pegawai\Schemas;

use App\Models\Pegawai;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pegawai')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->length(18)
                            ->regex('/^\d{18}$/')
                            ->validationMessages([
                                'regex' => 'NIP harus 18 digit angka.',
                                'unique' => 'NIP ini sudah terdaftar.',
                            ])
                            ->unique(ignoreRecord: true)
                            ->placeholder('199001012015031001'),
                        TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(255),
                        Select::make('golongan')
                            ->label('Golongan')
                            ->options(array_combine(Pegawai::GOLONGAN, Pegawai::GOLONGAN))
                            ->searchable()
                            ->required(),
                    ]),
            ]);
    }
}
