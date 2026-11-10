<?php

namespace App\Http\Requests\Api;

class LoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'nama_perangkat' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return ['email' => 'Email', 'password' => 'Password', 'nama_perangkat' => 'Nama perangkat'];
    }
}
