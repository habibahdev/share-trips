<?php

namespace App\Enum;

enum PaymentMethod: string
{
    case Card = 'card';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'CB',
            self::Cash => 'Espèce'
        };
    }
}
