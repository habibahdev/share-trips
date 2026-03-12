<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private ?User $usser = null;

    #[ORM\Column(length: 50)]
    private ?string $brand = null;

    #[ORM\Column(length: 50)]
    private ?string $model = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $plate = null;

    #[ORM\Column]
    private ?int $seats = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /** @var Collection<int, Trip> */
    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: Trip::class)]
    private Collection $trips;

    public function __construct()
    {
        $this->trips = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsser(): ?User
    {
        return $this->usser;
    }

    public function setUsser(?User $usser): static
    {
        $this->usser = $usser;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getPlate(): ?string
    {
        return $this->plate;
    }

    public function setPlate(string $plate): static
    {
        $this->plate = $plate;
        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): static
    {
        $this->seats = $seats;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLabel(): string
    {
        return "{$this->brand} {$this->model} - {$this->plate}";
    }

    /** @return Collection<int, Trip> */
    public function getTrips(): Collection
    {
        return $this->trips;
    }
}
