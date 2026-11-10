<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\KendaraanDinas\KendaraanDinasResource;
use App\Models\KendaraanDinas;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** 5 kendaraan dinas yang terakhir ditambahkan. */
class KendaraanDinasTerbaru extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Kendaraan Dinas Terbaru')
            ->description('5 data yang terakhir ditambahkan.')
            ->query(fn () => KendaraanDinas::query()
                ->with(['masterKendaraan', 'instansi', 'pegawai'])
                ->latest()
                ->latest('id')
                ->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('nomor_asset')->label('Nomor Asset')->fontFamily('mono')->size('sm'),
                TextColumn::make('nomor_polisi')->label('Nomor Polisi')->weight('bold'),
                TextColumn::make('masterKendaraan.label')->label('Kendaraan')->wrap(),
                // Singkatan supaya tabel tidak melebar; nama lengkap di tooltip.
                TextColumn::make('instansi.nama')->label('Perangkat Daerah')
                    ->formatStateUsing(fn (KendaraanDinas $record): string => $record->instansi?->singkatan ?: $record->instansi?->nama)
                    ->tooltip(fn (KendaraanDinas $record): ?string => $record->instansi?->nama),
                TextColumn::make('pegawai.nama')->label('Pemegang')->wrap(),
                TextColumn::make('masterKendaraan.kondisi')->label('Kondisi')->badge(),
                TextColumn::make('masterKendaraan.status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Ditambahkan')->since()
                    ->tooltip(fn (KendaraanDinas $record): string => $record->created_at->translatedFormat('d F Y H:i')),
            ])
            ->recordUrl(fn (KendaraanDinas $record): string => KendaraanDinasResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('semua')
                    ->label('Lihat semua')
                    ->link()
                    ->url(KendaraanDinasResource::getUrl()),
            ])
            ->emptyStateHeading('Belum ada kendaraan dinas')
            ->emptyStateDescription('Tambahkan data di menu Asset > Kendaraan Dinas.');
    }
}
