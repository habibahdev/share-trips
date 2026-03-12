<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class TripOpen extends Constraint
{
    public string $message = "Ce trajet n'est plus disponible à la réservation (statut : {{ status }}).";
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
