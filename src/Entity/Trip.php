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
    /**
     * Identifiant du trajet.
     *
     * @var integer|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Conducteur du trajet.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tripsAsDriver')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $driver = null;

    /**
     * Véhicule utilisé pour le trajet.
     *
     * @var Vehicle|null
     */
    #[ORM\ManyToOne(targetEntity: Vehicle::class, inversedBy: 'trips')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicle = null;

    /**
     * Ville de départ.
     *
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville de départ est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $origin = null;

    /**
     * Ville d'arrivée.
     *
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville d\'arrivée est obligatoire.')]
    #[Assert\Length(max: 100)]
    private ?string $destination = null;

    /**
     * Date et heur du départ.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    #[Assert\NotNull(message: 'La date de départ est obligatoire.')]
    #[Assert\GreaterThan(value: 'now', message: 'La date de départ doit être dans le futur.')]
    private ?\DateTimeImmutable $departureAt = null;

    /**
     * Nombre de places disponibles.
     */
    #[ORM\Column]
    #[Assert\NotNull(message: 'Le nombre de places est obligatoire.')]
    #[Assert\Range(
        min: 1,
        max: 8,
        notInRangeMessage: 'Le nombre de places doit être compris entre {{ min }} et {{ max }}'
    )]
    private ?int $availableSeats = null;

    /**
     * Prix par place.
     *
     * @var float|null
     */
    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Le prix est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif ou nul.')]
    private ?float $pricePerSeat = null;

    /**
     * Statut du projet.
     *
     * @var TripStatus
     */
    #[ORM\Column(enumType: TripStatus::class)]
    private TripStatus $status = TripStatus::Open;

    /**
     * Date de création du trajet.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Date de dernière mise à jour du trajet.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Réservations associées au trajet.
     *
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(mappedBy: 'trip', targetEntity: Booking::class)]
    private Collection $bookings;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\OneToMany(mappedBy: 'trip', targetEntity: Conversation::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $conversations;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->conversations = new ArrayCollection();
    }

    /**
     * Callabck avant persistance.
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
    public function getDriver(): ?User
    {
        return $this->driver;
    }

    /**
     * @param User|null $driver
     * @return static
     */
    public function setDriver(?User $driver): static
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @param Vehicle|null $vehicle
     * @return static
     */
    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return static
     */
    public function setOrigin(string $origin): static
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDestination(): ?string
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     * @return static
     */
    public function setDestination(string $destination): static
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDepartureAt(): ?\DateTimeImmutable
    {
        return $this->departureAt;
    }

    /**
     * @param \DateTimeImmutable $departureAt
     * @return static
     */
    public function setDepartureAt(\DateTimeImmutable $departureAt): static
    {
        $this->departureAt = $departureAt;
        return $this;
    }

    /**
     * @return integer|null
     */
    public function getAvailableSeats(): ?int
    {
        return $this->availableSeats;
    }

    /**
     * @param integer $availableSeats
     * @return static
     */
    public function setAvailableSeats(int $availableSeats): static
    {
        $this->availableSeats = $availableSeats;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPricePerSeat(): ?float
    {
        return $this->pricePerSeat;
    }

    /**
     * @param float $pricePerSeat
     * @return static
     */
    public function setPricePerSeat(float $pricePerSeat): static
    {
        $this->pricePerSeat = $pricePerSeat;
        return $this;
    }

    /**
     * @return TripStatus
     */
    public function getStatus(): TripStatus
    {
        return $this->status;
    }

    /**
     * @param TripStatus $status
     * @return static
     */
    public function setStatus(TripStatus $status): static
    {
        $this->status = $status;
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
     * Vérifie si le trajet est complet.
     *
     * @return boolean
     */
    public function isFull(): bool
    {
        return $this->status === TripStatus::Full;
    }

    /**
     * Vérifie si le trajet est ouvert.
     *
     * @return boolean
     */
    public function isOpen(): bool
    {
        return $this->status === TripStatus::Open;
    }

    /**
     * Vérifie si le trajet a été annulé.
     *
     * @return boolean
     */
    public function isCancelled(): bool
    {
        return $this->status === TripStatus::Cancelled;
    }

    /**
     * Vérifie si le trajet a déjà été effectué.
     *
     * @return boolean
     */
    public function isPast(): bool
    {
        return $this->departureAt !== null && $this->departureAt < new \DateTimeImmutable();
    }

    /**
     * Retourne le nombre de places restantes disponibles.
     *
     * @return integer
     */
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

    /**
     * @param Booking $booking
     * @return static
     */
    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setTrip($this);
        }
        return $this;
    }

    /**
     * @param Booking $booking
     * @return static
     */
    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            if ($booking->getTrip() === $this) {
                $booking->setTrip(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Conversation> */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setTrip($this);
        }
        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            if ($conversation->getTrip() === $this) {
                $conversation->setTrip(null);
            }
        }
        return $this;
    }
}
