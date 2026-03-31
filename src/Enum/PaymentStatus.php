<?php

namespace App\Enum;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this)
        {
            self::Pending => 'En attente',
            self::Completed => 'Payé',
            self::Failed => 'Échoué',
            self::Refunded => 'Remboursé'
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Failed, self::Refunded => true,
            self::Pending => false
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Completed;
    }
}