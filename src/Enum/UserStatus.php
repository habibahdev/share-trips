<?php

namespace App\Enum;

enum UserStatus: string
{
    case Active = 'active';
    case Banned = 'banned';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Actif',
            self::Banned => 'Banni',
            self::Suspended => 'Suspendu'
        };
    }

    public function isBlocked(): bool
    {
        return match ($this) {
            self::Banned, self::Suspended => true,
            default => false
        };
    }
}
