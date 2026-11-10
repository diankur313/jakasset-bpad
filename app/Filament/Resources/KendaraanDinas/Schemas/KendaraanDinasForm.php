<?php

namespace App\Filament\Resources\KendaraanDinas\Schemas;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Models\KendaraanDinas;
use App\Models\MasterKendaraan;
use App\Models\Pegawai;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

/**
 * Merek -> Tipe / Model -> Tahun Produksi adalah pilihan berjenjang dari Master Kendaraan.
 * Field turunan tidak dikunci (Filament menyembunyikan tanda * pada field terkunci); selama
 * induknya kosong, pilihannya kosong. Placeholder dibuat statis karena tidak diperbarui otomatis.
 * Hanya "Tahun Produksi" (master_kendaraan_id) yang disimpan; Merek dan Tipe / Model
 * dibaca dari relasi masterKendaraan.
 *
 * Nomor Asset sengaja tidak ada di form: dibuat otomatis di backend
 * (KendaraanDinas::creating) dan hanya ditampilkan di tabel & halaman detail.
 */
class KendaraanDinasForm
{
    /** @return array<int, string> [id pegawai => "Nama — NIP"] yang terdaftar di instansi ini. */
    public static function pegawaiInstansi(mixed $instansiId): array
    {
        if (blank($instansiId)) {
            return [];
        }

        return Pegawai::query()
            ->where('instansi_id', $instansiId)
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (Pegawai $pegawai): array => [$pegawai->id => "{$pegawai->nama} — {$pegawai->nip}"])
            ->all();
    }

    /** @return array<int, string> [id warna => nama] yang tersedia untuk master kendaraan ini. */
    public static function warnaTersedia(mixed $masterKendaraanId): array
    {
        if (blank($masterKendaraanId)) {
            return [];
        }

        return MasterKendaraan::find($masterKendaraanId)?->warna->pluck('nama', 'id')->all() ?? [];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Kendaraan')
                    ->description('Merek, Tipe / Model, Tahun Produksi, dan Warna diambil dari Master Data > Kendaraan.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nomor_polisi')
                            ->label('Nomor Polisi')
                            ->required()
                            ->maxLength(15)
                            ->regex('/^[A-Za-z]{1,2}\s?\d{1,4}\s?[A-Za-z]{0,3}$/')
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'regex' => 'Format nomor polisi tidak valid. Contoh: B 1234 ABC.',
                                'unique' => 'Nomor polisi ini sudah terdaftar.',
                            ])
                            ->placeholder('B 1234 ABC')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase']),

                        Select::make('merek')
                            ->label('Merek')
                            ->options(fn (): array => MasterKendaraan::query()
                                ->distinct()->orderBy('merek')->pluck('merek', 'merek')->all())
                            ->searchable()
                            ->required()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn (Select $component, ?KendaraanDinas $record) => $component
                                ->state($record?->masterKendaraan?->merek))
                            ->afterStateUpdated(function (Set $set): void {
                                $set('tipe_model', null);
                                $set('master_kendaraan_id', null);
                                $set('warna_kendaraan_id', null);
                            }),

                        Select::make('tipe_model')
                            ->label('Tipe / Model')
                            ->options(fn (Get $get): array => MasterKendaraan::query()
                                ->where('merek', $get('merek'))
                                ->distinct()->orderBy('tipe_model')->pluck('tipe_model', 'tipe_model')->all())
                            ->searchable()
                            ->required()
                            ->live()
                            ->dehydrated(false)
                            ->placeholder('Pilih tipe / model (sesuai merek)')
                            ->afterStateHydrated(fn (Select $component, ?KendaraanDinas $record) => $component
                                ->state($record?->masterKendaraan?->tipe_model))
                            ->afterStateUpdated(function (Set $set): void {
                                $set('master_kendaraan_id', null);
                                $set('warna_kendaraan_id', null);
                            }),

                        Select::make('master_kendaraan_id')
                            ->label('Tahun Produksi')
                            ->options(fn (Get $get): array => MasterKendaraan::query()
                                ->where('merek', $get('merek'))
                                ->where('tipe_model', $get('tipe_model'))
                                ->orderByDesc('tahun_produksi')
                                ->pluck('tahun_produksi', 'id')->all())
                            ->required()
                            ->live()
                            ->placeholder('Pilih tahun produksi (sesuai tipe)')
                            ->exists('master_kendaraan', 'id')
                            // Isi Kondisi & Status awal dari master; masih bisa diubah. Warna dikosongkan
                            // kecuali master hanya punya satu warna.
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $warna = static::warnaTersedia($state);
                                $set('warna_kendaraan_id', count($warna) === 1 ? array_key_first($warna) : null);

                                $master = MasterKendaraan::find($state);

                                if (! $master) {
                                    return;
                                }

                                $set('kondisi', $master->kondisi);
                                $set('status', $master->status);
                            }),

                        Select::make('warna_kendaraan_id')
                            ->label('Warna Kendaraan')
                            // Hanya warna yang tersedia untuk master kendaraan terpilih.
                            ->options(fn (Get $get): array => static::warnaTersedia($get('master_kendaraan_id')))
                            ->in(fn (Get $get): array => array_keys(static::warnaTersedia($get('master_kendaraan_id'))))
                            ->searchable()
                            ->required()
                            ->placeholder('Pilih warna (sesuai tahun produksi)')
                            ->validationMessages(['in' => 'Warna ini tidak tersedia untuk tipe kendaraan yang dipilih.'])
                            ->helperText('Pilihan warna diatur di Master Data > Kendaraan (Warna Tersedia).'),

                        Select::make('kondisi')
                            ->label('Kondisi')
                            ->options(KondisiKendaraan::class)
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options(StatusKendaraan::class)
                            ->required(),
                    ]),

                Section::make('Kepemilikan & Pemegang')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('instansi_id')
                            ->label('Perangkat Daerah (Pemilik)')
                            ->relationship('instansi', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            // Pemegang harus pegawai instansi ini, jadi dikosongkan saat instansi berganti.
                            ->afterStateUpdated(fn (Set $set) => $set('pegawai_id', null)),

                        Select::make('pegawai_id')
                            ->label('Pemegang (Pegawai)')
                            // Hanya pegawai dari perangkat daerah pemilik yang dipilih.
                            ->options(fn (Get $get): array => static::pegawaiInstansi($get('instansi_id')))
                            ->in(fn (Get $get): array => array_keys(static::pegawaiInstansi($get('instansi_id'))))
                            ->searchable()
                            ->required()
                            ->placeholder('Pilih pegawai (sesuai perangkat daerah)')
                            ->validationMessages(['in' => 'Pegawai ini tidak terdaftar di perangkat daerah yang dipilih.'])
                            ->helperText('Hanya pegawai dari perangkat daerah pemilik. Instansi pegawai diatur di menu Pegawai.'),
                    ]),
            ]);
    }
}
