<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class InstansiRequest extends ApiRequest
{
    protected function isianLama(Model $record): array
    {
        return $record->only(['nama', 'singkatan', 'alamat']);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', Rule::unique('instansi', 'nama')->ignore($this->record())],
            'singkatan' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return ['nama.unique' => 'Nama instansi ini sudah terdaftar.'] + parent::messages();
    }

    public function attributes(): array
    {
        return ['nama' => 'Nama instansi', 'singkatan' => 'Singkatan', 'alamat' => 'Alamat'];
    }
}
