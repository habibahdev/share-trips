<?php

namespace App\Validator;

use App\Entity\Booking;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotTripDriverValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotTripDriver) {
            throw new UnexpectedTypeException($constraint, NotTripDriver::class);
        }
        if (!$value instanceof Booking) {
            return;
        }
        if ($value->getTrip() === null || $value->getPassenger() === null) {
            return;
        }
        $driver = $value->getTrip()->getDriver();
        $passenger = $value->getPassenger();
        if ($driver->getId() === $passenger->getId()) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('passenger')
                ->addViolation()
            ;
        }
    }
}
