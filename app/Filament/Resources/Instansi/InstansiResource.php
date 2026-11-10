<?php

namespace App\Filament\Resources\Instansi;

use App\Filament\Resources\Instansi\Pages\CreateInstansi;
use App\Filament\Resources\Instansi\Pages\EditInstansi;
use App\Filament\Resources\Instansi\Pages\ListInstansi;
use App\Filament\Resources\Instansi\Pages\ViewInstansi;
use App\Filament\Resources\Instansi\Schemas\InstansiForm;
use App\Filament\Resources\Instansi\Schemas\InstansiInfolist;
use App\Filament\Resources\Instansi\Tables\InstansiTable;
use App\Models\Instansi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Master data Instansi Pemilik Kendaraan (CRUD).
 * Data yang sama ditampilkan read-only di menu Perangkat Daerah.
 */
class InstansiResource extends Resource
{
    protected static ?string $model = Instansi::class;

    protected static ?string $slug = 'master-data/instansi';

    protected static ?string $modelLabel = 'Instansi Pemilik';

    protected static ?string $pluralModelLabel = 'Instansi Pemilik Kendaraan';

    protected static ?string $navigationLabel = 'Instansi Pemilik';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nama';

    /** Data turunan yang dicek sebelum instansi dihapus. */
    public const DEPENDENCIES = [
        'kendaraanDinas' => ['label' => 'Kendaraan Dinas', 'title' => 'nomor_polisi'],
    ];

    public static function form(Schema $schema): Schema
    {
        return InstansiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstansiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstansiTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstansi::route('/'),
            'create' => CreateInstansi::route('/create'),
            'view' => ViewInstansi::route('/{record}'),
            'edit' => EditInstansi::route('/{record}/edit'),
        ];
    }
}
