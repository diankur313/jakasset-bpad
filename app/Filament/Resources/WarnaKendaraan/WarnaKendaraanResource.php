<?php

namespace App\Filament\Resources\WarnaKendaraan;

use App\Filament\Resources\WarnaKendaraan\Pages\CreateWarnaKendaraan;
use App\Filament\Resources\WarnaKendaraan\Pages\EditWarnaKendaraan;
use App\Filament\Resources\WarnaKendaraan\Pages\ListWarnaKendaraan;
use App\Filament\Resources\WarnaKendaraan\Pages\ViewWarnaKendaraan;
use App\Filament\Resources\WarnaKendaraan\Schemas\WarnaKendaraanForm;
use App\Filament\Resources\WarnaKendaraan\Schemas\WarnaKendaraanInfolist;
use App\Filament\Resources\WarnaKendaraan\Tables\WarnaKendaraanTable;
use App\Models\WarnaKendaraan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WarnaKendaraanResource extends Resource
{
    protected static ?string $model = WarnaKendaraan::class;

    protected static ?string $slug = 'master-data/warna-kendaraan';

    protected static ?string $modelLabel = 'Warna Kendaraan';

    protected static ?string $pluralModelLabel = 'Warna Kendaraan';

    protected static ?string $navigationLabel = 'Warna Kendaraan';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama';

    /** Data turunan yang dicek sebelum warna dihapus. */
    public const DEPENDENCIES = [
        'kendaraanDinas' => ['label' => 'Kendaraan Dinas', 'title' => 'nomor_polisi'],
    ];

    public static function form(Schema $schema): Schema
    {
        return WarnaKendaraanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WarnaKendaraanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarnaKendaraanTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWarnaKendaraan::route('/'),
            'create' => CreateWarnaKendaraan::route('/create'),
            'view' => ViewWarnaKendaraan::route('/{record}'),
            'edit' => EditWarnaKendaraan::route('/{record}/edit'),
        ];
    }
}
