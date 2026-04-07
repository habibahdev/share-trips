<?php

namespace App\Entity;

use App\Enum\BookingStatus;
use App\Enum\PaymentStatus;
use App\Repository\BookingRepository;
use App\Validator\AvailableSeats;
use App\Validator\NotTripDriver;
use App\Validator\TripOpen;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['trip', 'passenger'])]
#[NotTripDriver()]
#[AvailableSeats()]
#[TripOpen()]
class Booking
{
    /**
     * Identifiant de la réservation.
     *
     * @var integer|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Trajet associé à la réservation.
     *
     * @var Trip|null
     */
    #[ORM\ManyToOne(targetEntity: Trip::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le trajet est obligatoire.')]
    private ?Trip $trip = null;

    /**
     * Passager ayant effectué la réservation.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le passager est obligatoire.')]
    private ?User $passenger = null;

    /**
     * Nombre de places réservées.
     *
     * @var integer
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(value: 8, message: 'Vous ne pouvez pas réserver plus de 8 places.')]
    private int $seatsBooked = 1;

    /**
     * Statut de la réservation.
     *
     * @var BookingStatus
     */
    #[ORM\Column(enumType: BookingStatus::class)]
    private BookingStatus $status = BookingStatus::Pending;

    /**
     * Date de création de la réservation.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Date de dernière mise à jour de la réservation.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Rapports associés à la réservation.
     *
     * @var Collection
     */
    /** @var Collection<int, Report> */
    #[ORM\OneToMany(mappedBy: 'booking', targetEntity: Report::class, orphanRemoval: true)]
    private Collection $reports;

    /**
     * Paiement associé à la réservation.
     *
     * @var Payment|null
     */
    #[ORM\OneToOne(mappedBy: 'booking', targetEntity: Payment::class, cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    /**
     * Avis associés à la réservation.
     *
     * @var Collection
     */
    /** @var Collection<int, Review> */
    #[ORM\OneToMany(mappedBy: 'booking', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    /**
     * Undocumented function
     */
    public function __construct()
    {
        $this->reports = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    /**
     * Représentation textuelle de la réservation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->trip->getOrigin() . ' -> ' .
            $this->trip->getDestination() . ' (' .
            $this->passenger->getFullName() . ')'
        ;
    }

    /**
     * Callback exécuté avant la persistance.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Callback exécuté avant la mise à jour.
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
     * @return Trip|null
     */
    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    /**
     * @param Trip|null $trip
     * @return static
     */
    public function setTrip(?Trip $trip): static
    {
        $this->trip = $trip;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getPassenger(): ?User
    {
        return $this->passenger;
    }

    /**
     * @param User|null $passenger
     * @return static
     */
    public function setPassenger(?User $passenger): static
    {
        $this->passenger = $passenger;
        return $this;
    }

    /**
     * @return integer
     */
    public function getSeatsBooked(): int
    {
        return $this->seatsBooked;
    }

    /**
     * @param integer $seatsBooked
     * @return static
     */
    public function setSeatsBooked(int $seatsBooked): static
    {
        $this->seatsBooked = $seatsBooked;
        return $this;
    }

    /**
     * @return BookingStatus
     */
    public function getStatus(): BookingStatus
    {
        return $this->status;
    }

    /**
     * @param BookingStatus $status
     * @return static
     */
    public function setStatus(BookingStatus $status): static
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
     * Calcul le prix total de la résservation.
     *
     * @return float
     */
    public function getTotalPrice(): float
    {
        return ($this->trip->getPricePerSeat() ?? 0) * $this->seatsBooked;
    }

    /** @return Collection<int, Report> */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    /**
     * @param Report $report
     * @return static
     */
    public function addReport(Report $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setBooking($this);
        }
        return $this;
    }

    /**
     * @param Report $report
     * @return static
     */
    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            if ($report->getBooking() === $this) {
                $report->setBooking(null);
            }
        }
        return $this;
    }

    /**
     * Vérifie si la réservation a été signalée par un utilisateur.
     *
     * @param User $user
     * @return boolean
     */
    public function hasBeenReportedBy(User $user): bool
    {
        foreach ($this->reports as $report) {
            if ($report->getReporter()->getId() === $user->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @param Payment|null $payment
     * @return static
     */
    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * Vérifie si la réservation est payée.
     *
     * @return boolean
     */
    public function isPaid(): bool
    {
        return $this->payment?->getStatus() === PaymentStatus::Completed;
    }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /**
     * @param Review $review
     * @return static
     */
    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setBooking($this);
        }
        return $this;
    }

    /**
     * @param Review $review
     * @return static
     */
    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getBooking() === $this) {
                $review->setBooking(null);
            }
        }
        return $this;
    }
}
