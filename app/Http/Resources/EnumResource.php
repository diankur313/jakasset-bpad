<?php

namespace App\Http\Resources;

use BackedEnum;
use Filament\Support\Contracts\HasLabel;

/** Enum ditampilkan sebagai {"value": "...", "label": "..."}. */
class EnumResource
{
    public static function make(?BackedEnum $enum): ?array
    {
        if (! $enum) {
            return null;
        }

        return [
            'value' => $enum->value,
            'label' => $enum instanceof HasLabel ? $enum->getLabel() : $enum->name,
        ];
    }

    /** @param  class-string<BackedEnum>  $enum */
    public static function pilihan(string $enum): array
    {
        return array_map(fn (BackedEnum $case): array => static::make($case), $enum::cases());
    }
}
