<?php

namespace App\Filament\Resources\MasterKendaraan;

use App\Filament\Resources\MasterKendaraan\Pages\CreateMasterKendaraan;
use App\Filament\Resources\MasterKendaraan\Pages\EditMasterKendaraan;
use App\Filament\Resources\MasterKendaraan\Pages\ListMasterKendaraan;
use App\Filament\Resources\MasterKendaraan\Pages\ViewMasterKendaraan;
use App\Filament\Resources\MasterKendaraan\Schemas\MasterKendaraanForm;
use App\Filament\Resources\MasterKendaraan\Schemas\MasterKendaraanInfolist;
use App\Filament\Resources\MasterKendaraan\Tables\MasterKendaraanTable;
use App\Models\MasterKendaraan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MasterKendaraanResource extends Resource
{
    protected static ?string $model = MasterKendaraan::class;

    protected static ?string $slug = 'master-data/kendaraan';

    protected static ?string $modelLabel = 'Master Kendaraan';

    protected static ?string $pluralModelLabel = 'Master Kendaraan';

    protected static ?string $navigationLabel = 'Kendaraan';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'label';

    /** Data turunan yang dicek sebelum master kendaraan dihapus. */
    public const DEPENDENCIES = [
        'kendaraanDinas' => ['label' => 'Kendaraan Dinas', 'title' => 'nomor_polisi'],
    ];

    public static function form(Schema $schema): Schema
    {
        return MasterKendaraanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MasterKendaraanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterKendaraanTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['merek', 'tipe_model'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterKendaraan::route('/'),
            'create' => CreateMasterKendaraan::route('/create'),
            'view' => ViewMasterKendaraan::route('/{record}'),
            'edit' => EditMasterKendaraan::route('/{record}/edit'),
        ];
    }
}
