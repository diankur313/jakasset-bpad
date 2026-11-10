<?php

namespace App\Filament\Support;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

/**
 * Aksi hapus yang mengecek dependensi data sebelum menghapus.
 *
 * Format $relations:
 *   [
 *       'kendaraanDinas' => ['label' => 'Kendaraan Dinas', 'title' => 'nomor_polisi'],
 *   ]
 *
 * - 'label' : nama data turunan yang ditampilkan di alert.
 * - 'title' : kolom yang dipakai untuk menyebut contoh data turunan.
 *
 * Bila record masih dipakai, modal hapus berubah menjadi alert
 * "Data tidak dapat dihapus" tanpa tombol hapus.
 */
class DependencyGuard
{
    /** Jumlah contoh data turunan yang disebut di alert. */
    private const SAMPLE_LIMIT = 5;

    /**
     * @param  array<string, array{label: string, title: string}>  $relations
     * @return array<int, array{label: string, count: int, samples: array<int, string>}>
     */
    public static function dependents(Model $record, array $relations): array
    {
        $found = [];

        foreach ($relations as $relation => $config) {
            $query = $record->{$relation}();
            $count = (clone $query)->count();

            if ($count === 0) {
                continue;
            }

            $found[] = [
                'label' => $config['label'],
                'count' => $count,
                'samples' => (clone $query)->limit(self::SAMPLE_LIMIT)->pluck($config['title'])->all(),
            ];
        }

        return $found;
    }

    /**
     * @param  array<int, array{label: string, count: int, samples: array<int, string>}>  $dependents
     */
    public static function describe(array $dependents): HtmlString
    {
        $items = collect($dependents)->map(function (array $dep): string {
            $samples = e(implode(', ', $dep['samples']));
            $more = $dep['count'] > count($dep['samples']) ? ', dan lainnya' : '';

            return "<li><strong>{$dep['count']} {$dep['label']}</strong>: {$samples}{$more}</li>";
        })->implode('');

        return new HtmlString(
            'Data ini masih digunakan oleh:'
            ."<ul style=\"list-style: disc; text-align: left; margin: .5rem 0 .5rem 1.25rem;\">{$items}</ul>"
            .'Hapus atau pindahkan data tersebut terlebih dahulu.'
        );
    }

    /**
     * @param  array<string, array{label: string, title: string}>  $relations
     */
    public static function deleteAction(array $relations): DeleteAction
    {
        $blocked = fn (Model $record): bool => static::dependents($record, $relations) !== [];

        return DeleteAction::make()
            ->modalHeading(fn (DeleteAction $action, Model $record): string => $blocked($record)
                ? 'Data tidak dapat dihapus'
                : __('filament-actions::delete.single.modal.heading', ['label' => $action->getRecordTitle()]))
            ->modalDescription(fn (Model $record): HtmlString|string => $blocked($record)
                ? static::describe(static::dependents($record, $relations))
                : 'Data yang dihapus tidak dapat dikembalikan. Yakin ingin menghapus?')
            ->modalIcon(fn (Model $record) => $blocked($record) ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedTrash)
            ->modalIconColor(fn (Model $record): string => $blocked($record) ? 'warning' : 'danger')
            ->modalSubmitAction(fn (Model $record) => $blocked($record) ? false : null)
            ->modalCancelActionLabel(fn (Model $record): ?string => $blocked($record) ? 'Tutup' : null)
            // Pengaman: data bisa saja mulai dipakai saat modal sedang terbuka.
            ->before(function (DeleteAction $action, Model $record) use ($relations): void {
                $dependents = static::dependents($record, $relations);

                if ($dependents === []) {
                    return;
                }

                Notification::make()
                    ->danger()
                    ->title('Data tidak dapat dihapus')
                    ->body(static::describe($dependents))
                    ->persistent()
                    ->send();

                $action->halt();
            });
    }

    /**
     * @param  array<string, array{label: string, title: string}>  $relations
     */
    public static function deleteBulkAction(array $relations, string $titleAttribute): DeleteBulkAction
    {
        $partition = fn (Collection $records): Collection => $records->partition(
            fn (Model $record): bool => static::dependents($record, $relations) !== [],
        );

        return DeleteBulkAction::make()
            ->modalDescription(function (Collection $records) use ($partition, $titleAttribute): HtmlString|string {
                [$blocked] = $partition($records);

                if ($blocked->isEmpty()) {
                    return 'Data yang dihapus tidak dapat dikembalikan. Yakin ingin menghapus?';
                }

                $names = e($blocked->pluck($titleAttribute)->implode(', '));

                if ($blocked->count() === $records->count()) {
                    return new HtmlString("Semua data terpilih masih digunakan oleh data lain sehingga tidak dapat dihapus:<br><strong>{$names}</strong>");
                }

                return new HtmlString(
                    "<strong>{$blocked->count()} dari {$records->count()}</strong> data terpilih masih digunakan oleh data lain dan <strong>akan dilewati</strong>:<br>"
                    ."<strong>{$names}</strong><br><br>Data lainnya akan dihapus permanen. Lanjutkan?"
                );
            })
            ->modalIcon(fn (Collection $records) => $partition($records)[0]->isNotEmpty()
                ? Heroicon::OutlinedExclamationTriangle
                : Heroicon::OutlinedTrash)
            ->modalIconColor(fn (Collection $records): string => $partition($records)[0]->isNotEmpty() ? 'warning' : 'danger')
            ->modalSubmitAction(fn (Collection $records) => $partition($records)[1]->isEmpty() ? false : null)
            ->modalCancelActionLabel(fn (Collection $records): ?string => $partition($records)[1]->isEmpty() ? 'Tutup' : null)
            ->action(function (Collection $records) use ($partition, $titleAttribute): void {
                [$blocked, $deletable] = $partition($records);

                $deletable->each->delete();

                if ($blocked->isEmpty()) {
                    Notification::make()->success()->title("{$deletable->count()} data berhasil dihapus")->send();

                    return;
                }

                Notification::make()
                    ->warning()
                    ->title("{$deletable->count()} data dihapus, {$blocked->count()} data dilewati")
                    ->body('Masih digunakan oleh data lain: '.$blocked->pluck($titleAttribute)->implode(', '))
                    ->persistent()
                    ->send();
            });
    }
}
