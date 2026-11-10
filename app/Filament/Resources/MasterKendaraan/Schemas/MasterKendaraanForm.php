<?php

namespace App\Filament\Resources\MasterKendaraan\Schemas;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Models\MasterKendaraan;
use App\Models\WarnaKendaraan;
use Closure;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class MasterKendaraanForm
{
    /** Isian untuk menambah warna baru dari field Warna Tersedia. */
    public static function warnaFormSchema(): array
    {
        return [
            TextInput::make('nama')
                ->label('Nama Warna')
                ->required()
                ->maxLength(50)
                ->dehydrateStateUsing(fn (?string $state): string => WarnaKendaraan::rapikan($state))
                // Dicek setelah dirapikan, jadi "hitam", "HITAM", dan "Hitam" dianggap sama.
                ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                    if (WarnaKendaraan::where('nama', WarnaKendaraan::rapikan($value))->exists()) {
                        $fail('Warna ini sudah ada. Pilih dari daftar Warna Tersedia.');
                    }
                })
                ->placeholder('Hitam')
                ->helperText('Penulisan otomatis dirapikan, misalnya "biru  dongker" menjadi "Biru Dongker".'),
            ColorPicker::make('kode_hex')
                ->label('Contoh Warna')
                ->hex()
                ->regex('/^#[0-9A-Fa-f]{6}$/')
                ->helperText('Opsional.'),
        ];
    }

    /**
     * Warna yang masih dipakai kendaraan dinas bertipe ini tidak boleh dikeluarkan dari Warna Tersedia.
     */
    public static function cegahHapusWarnaTerpakai(?MasterKendaraan $record): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($record): void {
            if (! $record) {
                return;
            }

            $dipilih = array_map('intval', (array) $value);

            $terpakai = $record->kendaraanDinas()
                ->whereNotNull('warna_kendaraan_id')
                ->whereNotIn('warna_kendaraan_id', $dipilih)
                ->with('warnaKendaraan')
                ->get()
                ->groupBy(fn ($kendaraan) => $kendaraan->warnaKendaraan->nama);

            if ($terpakai->isEmpty()) {
                return;
            }

            $rincian = $terpakai
                ->map(fn ($kendaraan, $warna) => "{$warna} (dipakai {$kendaraan->pluck('nomor_polisi')->implode(', ')})")
                ->implode('; ');

            $fail("Warna tidak dapat dihapus karena masih dipakai kendaraan dinas: {$rincian}. Ubah warna kendaraan tersebut terlebih dahulu.");
        };
    }

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

                        Select::make('warna')
                            ->label('Warna Tersedia')
                            ->relationship('warna', 'nama')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Warna yang bisa dipilih di Kendaraan Dinas untuk tipe ini. Warna belum ada di daftar? Klik tombol + untuk menambahkannya.')
                            ->createOptionForm(static::warnaFormSchema())
                            ->createOptionModalHeading('Tambah Warna Baru')
                            ->rule(fn (?MasterKendaraan $record): Closure => static::cegahHapusWarnaTerpakai($record)),
                    ]),
            ]);
    }
}
