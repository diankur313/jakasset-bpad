<?php

namespace App\Filament\Resources\PerangkatDaerah;

use App\Filament\Resources\Instansi\Schemas\InstansiInfolist;
use App\Filament\Resources\Instansi\Tables\InstansiTable;
use App\Filament\Resources\PerangkatDaerah\Pages\ListPerangkatDaerah;
use App\Filament\Resources\PerangkatDaerah\Pages\ViewPerangkatDaerah;
use App\Filament\Resources\PerangkatDaerah\RelationManagers\KendaraanDinasRelationManager;
use App\Filament\Resources\PerangkatDaerah\RelationManagers\PegawaiRelationManager;
use App\Models\Instansi;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Menu Perangkat Daerah: hanya lihat data.
 * Sumber datanya adalah Master Data > Instansi Pemilik (tabel `instansi`).
 */
class PerangkatDaerahResource extends Resource
{
    protected static ?string $model = Instansi::class;

    protected static ?string $slug = 'perangkat-daerah';

    protected static ?string $modelLabel = 'Perangkat Daerah';

    protected static ?string $pluralModelLabel = 'Perangkat Daerah';

    protected static ?string $navigationLabel = 'Perangkat Daerah';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama';

    protected static bool $isGloballySearchable = false;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstansiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('kendaraanDinas'))
            ->defaultSort('nama')
            ->description('Data bersumber dari Master Data > Instansi Pemilik. Tambah atau ubah data di menu tersebut.')
            ->columns(InstansiTable::columns())
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            KendaraanDinasRelationManager::class,
            PegawaiRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPerangkatDaerah::route('/'),
            'view' => ViewPerangkatDaerah::route('/{record}'),
        ];
    }
}
