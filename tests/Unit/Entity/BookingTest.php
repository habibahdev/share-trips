<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\User;
use App\Enum\BookingStatus;
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase
{
    private Booking $booking;

    protected function setUp(): void
    {
        $this->booking = new Booking();
    }

    public function testDefaultStatusIsPending(): void
    {
        $this->assertSame(BookingStatus::Pending, $this->booking->getStatus());
    }

    public function testSetAndGetSeatsBooked(): void
    {
        $this->booking->setSeatsBooked(2);
        $this->assertSame(2, $this->booking->getSeatsBooked());
    }

    public function testSetAndGetPassenger(): void
    {
        $passenger = new User();
        $this->booking->setPassenger($passenger);
        $this->assertSame($passenger, $this->booking->getPassenger());
    }

    public function testSetAndGetTrip(): void
    {
        $trip = new Trip();
        $this->booking->setTrip($trip);
        $this->assertSame($trip, $this->booking->getTrip());
    }

    public function testSetAndGetStatus(): void
    {
        $this->booking->setStatus(BookingStatus::Confirmed);
        $this->assertSame(BookingStatus::Confirmed, $this->booking->getStatus());
    }

    public function testSetAndGetCancelledStatus(): void
    {
        $this->booking->setStatus(BookingStatus::Cancelled);
        $this->assertSame(BookingStatus::Cancelled, $this->booking->getStatus());
    }

    public function testOnPrePersistSetsTimestamps(): void
    {
        $this->booking->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->booking->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->booking->getUpdatedAt());
    }

    public function testOnPreUpdateSetsUpdatedAt(): void
    {
        $this->booking->onPrePersist();
        $createdAt = $this->booking->getCreatedAt();
        sleep(1);
        $this->booking->onPreUpdate();
        $this->assertSame($createdAt, $this->booking->getCreatedAt());
        $this->assertNotSame($createdAt, $this->booking->getUpdatedAt());
    }
}
