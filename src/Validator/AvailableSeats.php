<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AvailableSeats extends Constraint
{
    public string $message = "Seulement {{ available }} palce(s) disponible(s) sur ce trajet.";
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
