<?php

namespace App\Tests\Unit\Validator;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\Vehicle;
use App\Repository\BookingRepository;
use App\Validator\AvailableSeats;
use App\Validator\AvailableSeatsValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AvailableSeatsValidatorTest extends TestCase
{
    private AvailableSeatsValidator $validator;
    private ExecutionContextInterface&MockObject $context;
    private BookingRepository $bookingRepository;

    protected function setUp(): void
    {
        $this->bookingRepository = $this->createMock(BookingRepository::class);
        $this->validator = new AvailableSeatsValidator($this->bookingRepository);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testNoViolationWhenEnoughSeatsAvailable(): void
    {
        $trip = $this->createTripWithSeats(4);
        $booking = (new Booking())->setTrip($trip)->setSeatsBooked(2);
        // 1 place déjà prise, reste 3 places
        // on a demandé 2 places
        // donc c'est ok
        $this->bookingRepository->method('countConfirmedSeats')->willReturn(1);
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new AvailableSeats());
    }

    public function testViolantionWhenNotEnoughSeatsAvailable(): void
    {
        $trip = $this->createTripWithSeats(4);
        $booking = (new Booking())->setTrip($trip)->setSeatsBooked(3);
        // 3 places déjà prises, 1 place qui reste
        // on a demandé 3 place
        // donc erreur
        $this->bookingRepository->method('countConfirmedSeats')->willReturn(3);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->method('atPath')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->context->expects($this->once())->method('buildViolation')->willReturn($violationBuilder);
        $this->validator->validate($booking, new AvailableSeats());
    }

    public function testNoViolationWhenBookingExactlyFillsRemainingSeats(): void
    {
        $trip = $this->createTripWithSeats(4);
        $booking = (new Booking())->setTrip($trip)->setSeatsBooked(2);
        // 2 places déjà prises 2 disponibles
        // on en demande 2
        // docn OK
        $this->bookingRepository->method('countConfirmedSeats')->willReturn(2);
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new AvailableSeats());
    }

    public function testNoViolationWhenTripIsNull(): void
    {
        $booking = new Booking();
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new AvailableSeats());
    }

    public function testNoViolationWhenSeatsBookedIsNull(): void
    {
        $trip = $this->createTripWithSeats(4);
        $booking = (new Booking())->setTrip($trip);
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new AvailableSeats());
    }

    private function createTripWithSeats(int $seats): Trip
    {
        $vehicle = new Vehicle();
        $vehicle->setSeats($seats);
        $trip = new Trip();
        $trip->setVehicle($vehicle);
        return $trip;
    }
}