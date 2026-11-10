<?php

namespace App\Filament\Resources\MasterKendaraan\Schemas;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Models\MasterKendaraan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class MasterKendaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Master Kendaraan')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('merek')
                            ->label('Merek')
                            ->required()
                            ->maxLength(100)
                            ->datalist(fn (): array => MasterKendaraan::query()->distinct()->orderBy('merek')->pluck('merek')->all())
                            ->placeholder('Toyota'),
                        TextInput::make('tipe_model')
                            ->label('Tipe / Model')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Innova Zenix 2.0 G'),
                        TextInput::make('tahun_produksi')
                            ->label('Tahun Produksi')
                            ->required()
                            ->integer()
                            ->minValue(1950)
                            ->maxValue((int) date('Y') + 1)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                                    ->where('merek', $get('merek'))
                                    ->where('tipe_model', $get('tipe_model')),
                            )
                            ->validationMessages([
                                'unique' => 'Kombinasi Merek, Tipe / Model, dan Tahun Produksi ini sudah ada.',
                            ]),
                        Select::make('kondisi')
                            ->label('Kondisi')
                            ->options(KondisiKendaraan::class)
                            ->default(KondisiKendaraan::Baik)
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(StatusKendaraan::class)
                            ->default(StatusKendaraan::Active)
                            ->required(),
                    ]),
            ]);
    }
}
