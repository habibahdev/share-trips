<?php

namespace App\Validator;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AvailableSeatsValidator extends ConstraintValidator
{
    public function __construct(private readonly BookingRepository $bookingRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AvailableSeats) {
            throw new UnexpectedTypeException($constraint, AvailableSeats::class);
        }
        if (!$value instanceof Booking) {
            return;
        }
        if ($value->getTrip() === null) {
            return;
        }
        $trip = $value->getTrip();
        $takenSeats = $this->bookingRepository->countConfirmedSeats($trip, $value->getId());
        $available = $trip->getVehicle()->getSeats() - $takenSeats;
        if ($value->getSeatsBooked() > $available) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ available }}', (string)$available)
                ->atPath('seatsBooked')
                ->addViolation()
            ;
        }
    }
}
