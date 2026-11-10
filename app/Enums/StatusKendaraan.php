<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusKendaraan: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Standby = 'standby';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Standby => 'Standby',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'gray',
            self::Standby => 'info',
        };
    }
}
