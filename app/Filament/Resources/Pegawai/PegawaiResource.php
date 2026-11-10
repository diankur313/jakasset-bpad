<?php

namespace App\Filament\Resources\Pegawai;

use App\Filament\Resources\Pegawai\Pages\CreatePegawai;
use App\Filament\Resources\Pegawai\Pages\EditPegawai;
use App\Filament\Resources\Pegawai\Pages\ListPegawai;
use App\Filament\Resources\Pegawai\Pages\ViewPegawai;
use App\Filament\Resources\Pegawai\Schemas\PegawaiForm;
use App\Filament\Resources\Pegawai\Schemas\PegawaiInfolist;
use App\Filament\Resources\Pegawai\Tables\PegawaiTable;
use App\Models\Pegawai;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PegawaiResource extends Resource
{
    protected static ?string $model = Pegawai::class;

    protected static ?string $slug = 'pegawai';

    protected static ?string $modelLabel = 'Pegawai';

    protected static ?string $pluralModelLabel = 'Pegawai';

    protected static ?string $navigationLabel = 'Pegawai';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama';

    /** Data turunan yang dicek sebelum pegawai dihapus. */
    public const DEPENDENCIES = [
        'kendaraanDinas' => ['label' => 'Kendaraan Dinas', 'title' => 'nomor_polisi'],
    ];

    public static function form(Schema $schema): Schema
    {
        return PegawaiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PegawaiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PegawaiTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama', 'nip'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPegawai::route('/'),
            'create' => CreatePegawai::route('/create'),
            'view' => ViewPegawai::route('/{record}'),
            'edit' => EditPegawai::route('/{record}/edit'),
        ];
    }
}
