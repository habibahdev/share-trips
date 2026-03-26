<?php

namespace App\Enum;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Paypal = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Espèces',
            self::Paypal => 'Paypal',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => 'bi-cash',
            self::Paypal => 'bi-paypal',
        };
    }
}
