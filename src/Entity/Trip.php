<?php

namespace App\Entity;

use App\Enum\BookingStatus;
use App\Enum\TripStatus;
use App\Repository\TripRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TripRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Trip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tripsAsDriver')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $driver = null;

    #[ORM\ManyToOne(targetEntity: Vehicle::class, inversedBy: 'trips')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicle = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville de départ est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $origin = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville d\'arrivée est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $destination = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de départ est obligatoire.')]
    #[Assert\GreaterThan(value: 'now', message: 'La date de départ doit être dans le futur.')]
    private ?\DateTimeImmutable $departureAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le nombre de places est obligatoire.')]
    #[Assert\Range(
        min: 1,
        max: 8,
        notInRangeMessage: 'Le nombre de places doit être compris entre {{ min }} et {{ max }}')]
    private ?int $availableSeats = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Le prix est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif ou nul.')]
    private ?float $pricePerSeat = null;

    #[ORM\Column(enumType: TripStatus::class)]
    private TripStatus $status = TripStatus::Open;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Booking> */
    #[ORM\OneToMany(mappedBy: 'trip', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): static
    {
        $this->driver = $driver;
        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;
        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): static
    {
        $this->origin = $origin;
        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;
        return $this;
    }

    public function getDepartureAt(): ?\DateTimeImmutable
    {
        return $this->departureAt;
    }

    public function setDepartureAt(\DateTimeImmutable $departureAt): static
    {
        $this->departureAt = $departureAt;
        return $this;
    }

    public function getAvailableSeats(): ?int
    {
        return $this->availableSeats;
    }

    public function setAvailableSeats(int $availableSeats): static
    {
        $this->availableSeats = $availableSeats;
        return $this;
    }

    public function getPricePerSeat(): ?float
    {
        return $this->pricePerSeat;
    }

    public function setPricePerSeat(float $pricePerSeat): static
    {
        $this->pricePerSeat = $pricePerSeat;
        return $this;
    }

    public function getStatus(): TripStatus
    {
        return $this->status;
    }

    public function setStatus(TripStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isFull(): bool
    {
        return $this->status === TripStatus::Full;
    }

    public function isOpen(): bool
    {
        return $this->status === TripStatus::Open;
    }

    public function isCancelled(): bool
    {
        return $this->status === TripStatus::Cancelled;
    }

    public function isPast(): bool
    {
        return $this->departureAt !== null && $this->departureAt < new \DateTimeImmutable();
    }

    public function getRemainingSeats(): int
    {
        $confirmedSeats = 0;
        foreach ($this->bookings as $booking) {
            if ($booking->getStatus() === BookingStatus::Confirmed) {
                $confirmedSeats += $booking->getSeatsBooked();
            }
        }
        return max(0, ($this->availableSeats ?? 0) - $confirmedSeats);
    }

    /** @return Collection<int, Booking> */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setTrip($this);
        }
        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            if ($booking->getTrip() === $this) {
                $booking->setTrip(null);
            }
        }
        return $this;
    }
}
