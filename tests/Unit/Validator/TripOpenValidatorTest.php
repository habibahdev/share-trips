<?php

namespace App\Tests\Unit\Validator;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Enum\TripStatus;
use App\Validator\TripOpen;
use App\Validator\TripOpenValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TripOpenValidatorTest extends TestCase
{
    private TripOpenValidator $validator;
    private ExecutionContextInterface&MockObject $context;

    protected function setUp(): void
    {
        $this->validator = new TripOpenValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testNoViolationWhenTripIsOpen(): void
    {
        $trip = (new Trip())->setStatus(TripStatus::Open);
        $booking = (new Booking())->setTrip($trip);
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new TripOpen());
    }

    public function testViolvationWhenTripIsFull(): void
    {
        $trip = (new Trip())->setStatus(TripStatus::Full);
        $booking = (new Booking())->setTrip($trip);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->method('atPath')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->context->expects($this->once())->method('buildViolation')->willReturn($violationBuilder);
        $this->validator->validate($booking, new TripOpen());
    }

    public function testViolationWhenTripIsCancelled(): void
    {
        $trip = (new Trip())->setStatus(TripStatus::Cancelled);
        $booking = (new Booking())->setTrip($trip);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->method('atPath')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->context->expects($this->once())->method('buildViolation')->willReturn($violationBuilder);
        $this->validator->validate($booking, new TripOpen());
    }

    public function testDoesNothingIfTripIsNull(): void
    {
        $booking = new Booking();
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new TripOpen());
    }
}