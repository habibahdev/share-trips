<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\Vehicle;
use PHPUnit\Framework\TestCase;

class VehicleTest extends TestCase
{
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        $this->vehicle = new Vehicle();
    }

    public function testSetAndGetBrand(): void
    {
        $this->vehicle->setBrand('Renault');
        $this->assertSame('Renault', $this->vehicle->getBrand());
    }

    public function testSetAndGetModel(): void
    {
        $this->vehicle->setModel('Clio');
        $this->assertSame('Clio', $this->vehicle->getModel());
    }

    public function testSetAndGetColor(): void
    {
        $this->vehicle->setColor('Blanc');
        $this->assertSame('Blanc', $this->vehicle->getColor());
    }

    public function testSetAndGetLicensePlate(): void
    {
        $this->vehicle->setPlate('AZ-776-ER');
        $this->assertSame('AZ-776-ER', $this->vehicle->getPlate());
    }

    public function testSetAndGetSeats(): void
    {
        $this->vehicle->setSeats(4);
        $this->assertSame(4, $this->vehicle->getSeats());
    }

    public function testSetAndGetUser(): void
    {
        $user = new User();
        $this->vehicle->setUsser($user);
        $this->assertSame($user, $this->vehicle->getUsser());
    }

    public function testGetTripsReturnsEmptyCollectionByDefault(): void
    {
        $this->assertCount(0, $this->vehicle->getTrips());
    }
}
