<?php

namespace App\Http\Requests\Api;

/** Daftar ID untuk aksi massal (hapus massal, download label batch). */
class IdsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'distinct'],
        ];
    }

    public function attributes(): array
    {
        return ['ids' => 'Daftar ID', 'ids.*' => 'ID'];
    }
}
