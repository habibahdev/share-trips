<?php

namespace App\Validator;

use App\Entity\Booking;
use App\Enum\TripStatus;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TripOpenValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TripOpen) {
            throw new UnexpectedTypeException($constraint, TripOpen::class);
        }
        if (!$value instanceof Booking || $value->getTrip() === null) {
            return;
        }
        $status = $value->getTrip()->getStatus();
        if ($status !== TripStatus::Open) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ status }}', $status->value)
                ->atPath('trip')
                ->addViolation()
            ;
        }
    }
}
