<?php

namespace App\Tests\Unit\Validator;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\User;
use App\Validator\NotTripDriver;
use App\Validator\NotTripDriverValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotTripDriverValidatorTest extends TestCase
{
    private NotTripDriverValidator $validator;
    private ExecutionContextInterface&MockObject $context;

    protected function setUp(): void
    {
        $this->validator = new NotTripDriverValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testViolationWhenPassengerIsDriver(): void
    {
        $driver = $this->createUserWithId(1);
        $trip = (new Trip())->setDriver($driver);
        $booking = (new Booking())->setTrip($trip)->setPassenger($driver);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('atPath')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->context->expects($this->once())->method('buildViolation')->willReturn($violationBuilder);
        $this->validator->validate($booking, new NotTripDriver());
    }

    public function testPassengerCanBookTrip(): void
    {
        $driver = $this->createUserWithId(1);
        $passenger = $this->createUserWithId(2);
        $trip = (new Trip())->setDriver($driver);
        $booking = (new Booking())->setTrip($trip)->setPassenger($passenger);
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new NotTripDriver());
    }

    public function testDoesNothingIfTripIsFull(): void
    {
        $booking = new Booking();
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new NotTripDriver());
    }

    public function testDoesNothingIfPassengerIsNull(): void
    {
        $driver = $this->createUserWithId(1);
        $trip = (new Trip())->setDriver($driver);
        $booking = (new Booking())->setTrip($trip);
        $this->context->expects($this->never())->method('buildViolation');
        $this->validator->validate($booking, new NotTripDriver());
    }

    private function createUserWithId(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setValue($user, $id);
        return $user;
    }
}