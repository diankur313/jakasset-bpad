<?php

namespace App\Http\Requests\Api;

use App\Enums\KondisiKendaraan;
use App\Enums\StatusKendaraan;
use App\Filament\Resources\MasterKendaraan\Schemas\MasterKendaraanForm;
use App\Models\MasterKendaraan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/** Aturan sama dengan form Master Data > Kendaraan di panel admin. */
class MasterKendaraanRequest extends ApiRequest
{
    /** @param  MasterKendaraan  $record */
    protected function isianLama(Model $record): array
    {
        return [
            'merek' => $record->merek,
            'tipe_model' => $record->tipe_model,
            'tahun_produksi' => $record->tahun_produksi,
            'kondisi' => $record->kondisi?->value,
            'status' => $record->status?->value,
            'warna' => $record->warna()->pluck('warna_kendaraan.id')->all(),
        ];
    }

    public function rules(): array
    {
        /** @var MasterKendaraan|null $record */
        $record = $this->record();

        return [
            'merek' => ['required', 'string', 'max:100'],
            'tipe_model' => ['required', 'string', 'max:100'],
            'tahun_produksi' => [
                'required', 'integer', 'min:1950', 'max:'.((int) date('Y') + 1),
                Rule::unique('master_kendaraan', 'tahun_produksi')
                    ->where('merek', $this->input('merek'))
                    ->where('tipe_model', $this->input('tipe_model'))
                    ->ignore($record),
            ],
            'kondisi' => ['required', Rule::enum(KondisiKendaraan::class)],
            'status' => ['required', Rule::enum(StatusKendaraan::class)],
            'warna' => ['required', 'array', 'min:1', MasterKendaraanForm::cegahHapusWarnaTerpakai($record)],
            'warna.*' => ['integer', 'distinct', 'exists:warna_kendaraan,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_produksi.unique' => 'Kombinasi Merek, Tipe / Model, dan Tahun Produksi ini sudah ada.',
            'kondisi.enum' => 'Kondisi tidak valid. Pilihan: baik, rusak_ringan, rusak_berat.',
            'status.enum' => 'Status tidak valid. Pilihan: active, inactive, standby.',
        ] + parent::messages();
    }

    public function attributes(): array
    {
        return [
            'merek' => 'Merek', 'tipe_model' => 'Tipe / Model', 'tahun_produksi' => 'Tahun produksi',
            'kondisi' => 'Kondisi', 'status' => 'Status', 'warna' => 'Warna tersedia', 'warna.*' => 'Warna',
        ];
    }
}
