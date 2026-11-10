<?php

namespace App\Http\Requests\Api;

use App\Filament\Resources\MasterKendaraan\Schemas\MasterKendaraanForm;

/** Sama dengan tombol + "Tambah Warna Baru" di form Master Data > Kendaraan. */
class WarnaKendaraanRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:50', MasterKendaraanForm::cegahWarnaDobel()],
            'kode_hex' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return ['kode_hex.regex' => 'Kode warna harus format hex, contoh #1A1A1A.'] + parent::messages();
    }

    public function attributes(): array
    {
        return ['nama' => 'Nama warna', 'kode_hex' => 'Kode warna'];
    }
}
