<?php

namespace App\Enum;

enum PaymentMethod: string
{
    case Card = 'card';
    case Cash = 'espèce';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'CB',
            self::Cash => 'Espèce'
        };
    }
}
