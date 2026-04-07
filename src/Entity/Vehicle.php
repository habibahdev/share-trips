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
    /**
     * Identifiant du véhicule.
     *
     * @var integer|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Propriétaire du véhicule.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private ?User $usser = null;

    /**
     * Marque du véhicule.
     *
     * @var string|null
     */
    #[ORM\Column(length: 50)]
    private ?string $brand = null;

    /**
     * Modèle du véhicule.
     *
     * @var string|null
     */
    #[ORM\Column(length: 50)]
    private ?string $model = null;

    /**
     * Couleur du véhicule.
     *
     * @var string|null
     */
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $color = null;

    /**
     * Plaque d'immatriculation du véhicule.
     *
     * @var string|null
     */
    #[ORM\Column(length: 20, unique: true)]
    private ?string $plate = null;

    /**
     * Nombre de places du véhicule.
     *
     * @var integer|null
     */
    #[ORM\Column]
    private ?int $seats = null;

    /**
     * Date de création du véhicule.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Date de dernière mmodification du véhicule.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Trajets effectués avec ce véhicule.
     *
     * @var Collection<int, Trip>
     */
    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: Trip::class)]
    private Collection $trips;

    public function __construct()
    {
        $this->trips = new ArrayCollection();
    }

    /**
     * Représentation textetuelle du véhicule.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->brand . ' - ' . $this->model;
    }

    /**
     * Callback avant persistance.
     *
     * @return void
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Callback avant mise à jour.
     *
     * @return void
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUsser(): ?User
    {
        return $this->usser;
    }

    /**
     * @param User|null $usser
     * @return static
     */
    public function setUsser(?User $usser): static
    {
        $this->usser = $usser;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     * @return static
     */
    public function setBrand(string $brand): static
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @param string $model
     * @return static
     */
    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string|null $color
     * @return static
     */
    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlate(): ?string
    {
        return $this->plate;
    }

    /**
     * @param string $plate
     * @return static
     */
    public function setPlate(string $plate): static
    {
        $this->plate = $plate;
        return $this;
    }

    /**
     * @return integer|null
     */
    public function getSeats(): ?int
    {
        return $this->seats;
    }

    /**
     * @param integer $seats
     * @return static
     */
    public function setSeats(int $seats): static
    {
        $this->seats = $seats;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return "{$this->brand} {$this->model} - {$this->plate}";
    }

    /**
     * @return Collection<int, Trip>
     */
    public function getTrips(): Collection
    {
        return $this->trips;
    }
}
