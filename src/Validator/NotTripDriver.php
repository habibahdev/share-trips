<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class NotTripDriver extends Constraint
{
    public string $message = "Vous ne pouvez pas réserver votre propre trajet.";

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
