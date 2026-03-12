<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Booking;
use App\Entity\User;
use App\Entity\Vehicle;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGetFullName(): void
    {
        $this->user->setFirstName('Jean');
        $this->user->setLastName('Dupont');
        $this->assertSame('Jean Dupont', $this->user->getFullName());
    }

    public function testRolesAlwaysContainRoleUser(): void
    {
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testSetRoles(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $roles = $this->user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $this->user->setEmail('jean@example.com');
        $this->assertSame('jean@example.com', $this->user->getUserIdentifier());
    }

    public function testSetAndGetPhone(): void
    {
        $this->user->setPhone('0612345678');
        $this->assertSame('0612345678', $this->user->getPhone());
    }

    public function testAddVehicle(): void
    {
        $vehicle = new Vehicle();
        $this->user->addVehicle($vehicle);
        $this->assertCount(1, $this->user->getVehicles());
        $this->assertTrue($this->user->getVehicles()->contains($vehicle));
    }

    public function testAddVehicleDoesNotDuplicate(): void
    {
        $vehicle = new Vehicle();
        $this->user->addVehicle($vehicle);
        $this->user->addVehicle($vehicle);
        $this->assertCount(1, $this->user->getVehicles());
    }

    public function testRemoveVehicle(): void
    {
        $vehicle = new Vehicle();
        $this->user->addVehicle($vehicle);
        $this->user->removeVehicle($vehicle);
        $this->assertCount(0, $this->user->getVehicles());
    }

    public function testAddBooking(): void
    {
        $booking = new Booking();
        $this->user->addBooking($booking);
        $this->assertCount(1, $this->user->getBookings());
        $this->assertSame($this->user, $booking->getPassenger());
    }

    public function testRemoveBooking(): void
    {
        $booking = new Booking();
        $this->user->addBooking($booking);
        $this->user->removeBooking($booking);
        $this->assertCount(0, $this->user->getBookings());
    }

    public function testOnPrePersistSetsTimestamps(): void
    {
        $this->user->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getUpdatedAt());
    }
}
