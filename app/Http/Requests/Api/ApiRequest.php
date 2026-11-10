<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Dasar semua request REST API.
 *
 * - Pesan validasi berbahasa Indonesia.
 * - PATCH = ubah sebagian: field yang tidak dikirim diisi dari data lama sebelum divalidasi,
 *   sehingga aturan antar-field (mis. warna harus tersedia di master) tetap dicek.
 *   PUT = ubah penuh: semua field wajib dikirim.
 */
abstract class ApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Record yang sedang diubah (dari route), atau null saat menambah data. */
    protected function record(): ?Model
    {
        return collect($this->route()?->parameters() ?? [])
            ->first(fn (mixed $parameter): bool => $parameter instanceof Model);
    }

    /** Nilai field record lama, dipakai untuk melengkapi request PATCH. */
    protected function isianLama(Model $record): array
    {
        return [];
    }

    /** Merapikan input sebelum divalidasi. */
    protected function rapikanInput(): void {}

    protected function prepareForValidation(): void
    {
        if ($this->isMethod('PATCH') && ($record = $this->record())) {
            $this->replace(array_replace($this->isianLama($record), $this->all()));
        }

        $this->rapikanInput();
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka bulat.',
            'array' => ':attribute harus berupa daftar (array).',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'exists' => ':attribute tidak ditemukan.',
            'unique' => ':attribute sudah terdaftar.',
            'in' => ':attribute tidak valid.',
            'enum' => ':attribute tidak valid.',
            'distinct' => ':attribute berisi nilai ganda.',
            'regex' => 'Format :attribute tidak valid.',
            'size.string' => ':attribute harus :size karakter.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.numeric' => ':attribute maksimal :max.',
            'max.array' => ':attribute maksimal :max item.',
            'min.numeric' => ':attribute minimal :min.',
            'min.array' => ':attribute minimal :min item.',
        ];
    }
}
