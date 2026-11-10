<?php

namespace App\Filament\Resources\KendaraanDinas;

use App\Filament\Resources\KendaraanDinas\Pages\CreateKendaraanDinas;
use App\Filament\Resources\KendaraanDinas\Pages\EditKendaraanDinas;
use App\Filament\Resources\KendaraanDinas\Pages\ListKendaraanDinas;
use App\Filament\Resources\KendaraanDinas\Pages\ViewKendaraanDinas;
use App\Filament\Resources\KendaraanDinas\Schemas\KendaraanDinasForm;
use App\Filament\Resources\KendaraanDinas\Schemas\KendaraanDinasInfolist;
use App\Filament\Resources\KendaraanDinas\Tables\KendaraanDinasTable;
use App\Models\KendaraanDinas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Data transaksi Asset > Kendaraan Dinas.
 * Memakai: Master Kendaraan, Perangkat Daerah (Instansi), dan Pegawai.
 */
class KendaraanDinasResource extends Resource
{
    protected static ?string $model = KendaraanDinas::class;

    protected static ?string $slug = 'asset/kendaraan-dinas';

    protected static ?string $modelLabel = 'Kendaraan Dinas';

    protected static ?string $pluralModelLabel = 'Kendaraan Dinas';

    protected static ?string $navigationLabel = 'Kendaraan Dinas';

    protected static string|UnitEnum|null $navigationGroup = 'Asset';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nomor_polisi';

    /** Kendaraan dinas tidak punya data turunan. */
    public const DEPENDENCIES = [];

    public static function form(Schema $schema): Schema
    {
        return KendaraanDinasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KendaraanDinasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KendaraanDinasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKendaraanDinas::route('/'),
            'create' => CreateKendaraanDinas::route('/create'),
            'view' => ViewKendaraanDinas::route('/{record}'),
            'edit' => EditKendaraanDinas::route('/{record}/edit'),
        ];
    }
}
