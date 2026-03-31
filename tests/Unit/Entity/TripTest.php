<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Enum\TripStatus;
use PHPUnit\Framework\TestCase;

class TripTest extends TestCase
{
    private Trip $trip;

    protected function setUp(): void
    {
        $this->trip = new Trip();
    }

    public function testDefaultStatusIsOpen(): void
    {
        $this->assertSame(TripStatus::Open, $this->trip->getStatus());
    }

    public function testIfOpenReturnsTrueWhenStatusIsOpen(): void
    {
        $this->trip->setStatus(TripStatus::Open);
        $this->assertTrue($this->trip->isOpen());
    }

    public function testIfFullReturnsTrueWhenStatusIsFull(): void
    {
        $this->trip->setStatus(TripStatus::Full);
        $this->assertTrue($this->trip->isFull());
    }

    public function testIfOpenReturnsFalseWhenStatusIsFull(): void
    {
        $this->trip->setStatus(TripStatus::Full);
        $this->assertFalse($this->trip->isOpen());
    }

    public function testSetAndGetOrigin(): void
    {
        $this->trip->setOrigin('Paris');
        $this->assertSame('Paris', $this->trip->getOrigin());
    }

    public function testSetAndGetDestination(): void
    {
        $this->trip->setDestination('Lyon');
        $this->assertSame('Lyon', $this->trip->getDestination());
    }

    public function testSetAndGetPricePerSeat(): void
    {
        $this->trip->setPricePerSeat(15.50);
        $this->assertSame(15.50, $this->trip->getPricePerSeat());
    }

    public function testSetAndGetAvailableSeats(): void
    {
        $this->trip->setAvailableSeats(3);
        $this->assertSame(3, $this->trip->getAvailableSeats());
    }

    public function testSetAndGetDriver(): void
    {
        $driver = new User();
        $this->trip->setDriver($driver);
        $this->assertSame($driver, $this->trip->getDriver());
    }

    public function testSetAndGetVehicle(): void
    {
        $vehicle = new Vehicle();
        $this->trip->setVehicle($vehicle);
        $this->assertSame($vehicle, $this->trip->getVehicle());
    }

    public function testAddBooking(): void
    {
        $booking = new Booking();
        $this->trip->addBooking($booking);
        $this->assertCount(1, $this->trip->getBookings());
        $this->assertTrue($this->trip->getBookings()->contains($booking));
    }

    public function testAddBookingDoesNotDuplicate(): void
    {
        $booking = new Booking();
        $this->trip->addBooking($booking);
        $this->trip->addBooking($booking);
        $this->assertCount(1, $this->trip->getBookings());
    }

    public function testRemoveBooking(): void
    {
        $booking = new Booking();
        $this->trip->addBooking($booking);
        $this->trip->removeBooking($booking);
        $this->assertCount(0, $this->trip->getBookings());
    }

    public function testOnPrePersistSetsTimestamps(): void
    {
        $this->trip->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->trip->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->trip->getUpdatedAt());
    }

    public function testOnPreUpdateSetsUpdatedAt(): void
    {
        $this->trip->onPrePersist();
        $createdAt = $this->trip->getCreatedAt();
        sleep(1);
        $this->trip->onPreUpdate();
        $this->assertSame($createdAt, $this->trip->getCreatedAt());
        $this->assertNotSame($createdAt, $this->trip->getUpdatedAt());
    }
}
