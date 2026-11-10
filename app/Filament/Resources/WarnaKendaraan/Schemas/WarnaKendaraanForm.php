<?php

namespace App\Filament\Resources\WarnaKendaraan\Schemas;

use App\Models\WarnaKendaraan;
use Closure;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class WarnaKendaraanForm
{
    /** Title Case dengan spasi tunggal, sama dengan mutator di model WarnaKendaraan. */
    public static function rapikan(?string $nama): string
    {
        return Str::title(preg_replace('/\s+/', ' ', trim((string) $nama)));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Warna Kendaraan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Warna')
                            ->required()
                            ->maxLength(50)
                            ->dehydrateStateUsing(fn (?string $state): string => static::rapikan($state))
                            // Cek unik memakai nama yang sudah dirapikan, jadi "hitam", "HITAM", dan "Hitam" dianggap sama.
                            ->rule(fn (?WarnaKendaraan $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                $sudahAda = WarnaKendaraan::query()
                                    ->where('nama', static::rapikan($value))
                                    ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                                    ->exists();

                                if ($sudahAda) {
                                    $fail('Warna ini sudah terdaftar.');
                                }
                            })
                            ->placeholder('Hitam')
                            ->helperText('Penulisan otomatis dirapikan, misalnya "biru  dongker" menjadi "Biru Dongker".'),
                        ColorPicker::make('kode_hex')
                            ->label('Contoh Warna')
                            ->hex()
                            ->regex('/^#[0-9A-Fa-f]{6}$/')
                            ->helperText('Opsional. Dipakai untuk penanda warna di tabel.'),
                    ]),
            ]);
    }
}
