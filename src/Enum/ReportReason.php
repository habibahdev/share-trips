<?php

namespace App\Enum;

enum ReportReason: string
{
    case InappropriatedBehavior = 'inappropriate_behavior';
    case NoShow = 'no_show';
    case Fraud = 'fraud';
    case Harassment = 'harassment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::InappropriatedBehavior => 'Comportement inapproprié',
            self::NoShow => 'Non-présetation au trajet',
            self::Fraud => 'Arnaque / fraude',
            self::Harassment => 'Harcèlement',
            self::Other => 'Autre',
        };
    }
}
