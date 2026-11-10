<?php

namespace App\Http\Requests\Api;

use App\Filament\Resources\Pegawai\Schemas\PegawaiForm;
use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/** Aturan sama dengan form Pegawai di panel admin. */
class PegawaiRequest extends ApiRequest
{
    protected function isianLama(Model $record): array
    {
        return $record->only(['nama', 'nip', 'jabatan', 'golongan', 'instansi_id']);
    }

    public function rules(): array
    {
        /** @var Pegawai|null $record */
        $record = $this->record();

        return [
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'size:18', 'regex:/^\d{18}$/', Rule::unique('pegawai', 'nip')->ignore($record)],
            'jabatan' => ['required', 'string', 'max:255'],
            'golongan' => ['required', Rule::in(Pegawai::GOLONGAN)],
            'instansi_id' => ['required', 'integer', 'exists:instansi,id', PegawaiForm::cegahPindahInstansi($record)],
        ];
    }

    public function messages(): array
    {
        return [
            'nip.regex' => 'NIP harus 18 digit angka.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'golongan.in' => 'Golongan tidak valid. Pilihan: '.implode(', ', Pegawai::GOLONGAN).'.',
        ] + parent::messages();
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama', 'nip' => 'NIP', 'jabatan' => 'Jabatan',
            'golongan' => 'Golongan', 'instansi_id' => 'Instansi',
        ];
    }
}
