<?php

namespace App\Filament\Resources\Pegawai\Schemas;

use App\Models\Pegawai;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PegawaiForm
{
    /**
     * Pegawai yang masih memegang kendaraan dinas tidak boleh dipindah ke instansi lain,
     * karena kendaraan itu milik instansi lamanya.
     */
    public static function cegahPindahInstansi(?Pegawai $record): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($record): void {
            if (! $record) {
                return;
            }

            $kendaraan = $record->kendaraanDinas()->where('instansi_id', '!=', $value)->pluck('nomor_polisi');

            if ($kendaraan->isNotEmpty()) {
                $fail("Instansi tidak dapat diubah karena pegawai ini masih memegang kendaraan dinas milik instansi lain: {$kendaraan->implode(', ')}. Ganti pemegang kendaraan tersebut terlebih dahulu.");
            }
        };
    }

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
                        Select::make('instansi_id')
                            ->label('Instansi (Perangkat Daerah)')
                            ->relationship('instansi', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Pegawai hanya bisa menjadi pemegang kendaraan dinas milik instansinya.')
                            ->rule(fn (?Pegawai $record): Closure => static::cegahPindahInstansi($record)),
                    ]),
            ]);
    }
}
