<?php

namespace App\Http\Requests\Api;

use App\Filament\Resources\KendaraanDinas\Schemas\KendaraanDinasForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * Aturan sama dengan form Asset > Kendaraan Dinas di panel admin.
 * Nomor Asset, Kondisi, dan Status tidak bisa dikirim: nomor asset dibuat otomatis,
 * kondisi & status mengikuti Master Kendaraan.
 */
class KendaraanDinasRequest extends ApiRequest
{
    protected function isianLama(Model $record): array
    {
        return $record->only(['nomor_polisi', 'master_kendaraan_id', 'warna_kendaraan_id', 'instansi_id', 'pegawai_id']);
    }

    protected function rapikanInput(): void
    {
        if (is_string($this->input('nomor_polisi'))) {
            $this->merge(['nomor_polisi' => strtoupper(preg_replace('/\s+/', ' ', trim($this->input('nomor_polisi'))))]);
        }
    }

    public function rules(): array
    {
        return [
            'nomor_polisi' => [
                'required', 'string', 'max:15', 'regex:/^[A-Za-z]{1,2}\s?\d{1,4}\s?[A-Za-z]{0,3}$/',
                Rule::unique('kendaraan_dinas', 'nomor_polisi')->ignore($this->record()),
            ],
            'master_kendaraan_id' => ['required', 'integer', 'exists:master_kendaraan,id'],
            'warna_kendaraan_id' => [
                'required', 'integer',
                Rule::in(array_keys(KendaraanDinasForm::warnaTersedia($this->input('master_kendaraan_id')))),
            ],
            'instansi_id' => ['required', 'integer', 'exists:instansi,id'],
            'pegawai_id' => [
                'required', 'integer',
                Rule::in(array_keys(KendaraanDinasForm::pegawaiInstansi($this->input('instansi_id')))),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_polisi.regex' => 'Format nomor polisi tidak valid. Contoh: B 1234 ABC.',
            'nomor_polisi.unique' => 'Nomor polisi ini sudah terdaftar.',
            'warna_kendaraan_id.in' => 'Warna ini tidak tersedia untuk master kendaraan yang dipilih.',
            'pegawai_id.in' => 'Pegawai ini tidak terdaftar di perangkat daerah yang dipilih.',
        ] + parent::messages();
    }

    public function attributes(): array
    {
        return [
            'nomor_polisi' => 'Nomor polisi', 'master_kendaraan_id' => 'Master kendaraan',
            'warna_kendaraan_id' => 'Warna kendaraan', 'instansi_id' => 'Perangkat daerah',
            'pegawai_id' => 'Pemegang (pegawai)',
        ];
    }
}
