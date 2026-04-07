<?php

namespace App\Entity;

use App\Enum\UserStatus;
use App\Repository\UserRepository;
use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Identifiant de l'utilisateur.
     *
     * @var integer|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Email de l'utilisateur.
     *
     * @var string|null
     */
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * Rôle de l'utilisateur
     *
     * @var list<string>
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * Prénom
     *
     * @var string|null
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $firstName = null;

    /**
     * Nom
     *
     * @var string|null
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastName = null;

    /**
     * Numéro de téléphone
     *
     * @var string|null
     */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    /**
     * Date de création du compte.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Date de dernière mise à jour
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Véhicule associé à l'utilisateur.
     *
     * @var Collection<int, Vehicle>
     */
    #[ORM\OneToMany(mappedBy: 'usser', targetEntity: Vehicle::class, orphanRemoval: true)]
    private Collection $vehicles;

    /**
     * Trajets où l'utilisateur est le conducteur.
     *
     * @var Collection<int, Trip>
     */
    #[ORM\OneToMany(mappedBy: 'driver', targetEntity: Trip::class)]
    private Collection $tripsAsDriver;

    /**
     * Réservation effectuées par l'utilisateur.
     *
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(mappedBy: 'passenger', targetEntity: Booking::class)]
    private Collection $bookings;

    /**
     * Signalements effectués par l'utilisateur.
     *
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(mappedBy: 'reporter', targetEntity: Report::class)]
    private Collection $reportsMade;

    /**
     * Signalement que l'utilisateur a reçu.
     *
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(mappedBy: 'reported', targetEntity: Report::class)]
    private Collection $reportsReceived;

    /**
     * Statut de l'utilisateur.
     *
     * @var UserStatus
     */
    #[ORM\Column(enumType: UserStatus::class)]
    private UserStatus $status = UserStatus::Active;

    /**
     * Date jusqu'à laquelle l'utlisateur est suspendu.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $suspendedUntil = null;

    /**
     * Note administrative.
     *
     * @var string|null
     */
    #[ORM\Column(nullable: true, type: 'text')]
    private ?string $adminNote = null;

    /**
     * Vérification de l'email
     *
     * @var boolean|null
     */
    #[ORM\Column]
    private ?bool $isVerified = null;

    /**
     * Token d'inscription.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tokenRegister = null;

    /**
     * Durée de vie du token d'inscription.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $tokenRegisterLifetime = null;

    /**
     * Token de réinitialisation du mot de passe.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tokenForgotPassword = null;

    /**
     * Durée de vie du token de réinitialisation du mot de passe.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $tokenForgotPasswordExpiredAt = null;

    /**
     * Avis rédigés par l'utilisateur.
     *
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(mappedBy: 'reviewer', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviewsMade;

    /**
     * Avis reçu par l'utilisateur.
     *
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(mappedBy: 'reviewed', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviewsReceived;

    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->tripsAsDriver = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->reportsMade = new ArrayCollection();
        $this->reportsReceived = new ArrayCollection();
        $this->isVerified = false;
        $this->tokenRegisterLifetime = (new \DateTimeImmutable('now'))->add(new DateInterval('PT10M'));
        $this->reviewsMade = new ArrayCollection();
        $this->reviewsReceived = new ArrayCollection();
    }

    /**
     * Représentation texttuelle de l'utilisateur.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Callaback avant persistance.
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
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        if ($this->email === null || $this->email === '') {
            throw new \LogicException('Adresse e-mail doit être définie.');
        }
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return static
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return static
     */
    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return static
     */
    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return static
     */
    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
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
     * @return Collection<int, Vehicle>
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    /**
     * @param Vehicle $vehicle
     * @return static
     */
    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setUsser($this);
        }
        return $this;
    }

    /**
     * @param Vehicle $vehicle
     * @return static
     */
    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            if ($vehicle->getUsser() === $this) {
                $vehicle->setUsser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Trip>
     */
    public function getTripsAsDriver(): Collection
    {
        return $this->tripsAsDriver;
    }

    /**
     * @return Collection<int, Booking>
     */
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
            $booking->setPassenger($this);
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
            if ($booking->getPassenger() === $this) {
                $booking->setPassenger(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReportsMade(): Collection
    {
        return $this->reportsMade;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReportsReceived(): Collection
    {
        return $this->reportsReceived;
    }

    /**
     * @return UserStatus|null
     */
    public function getStatus(): ?UserStatus
    {
        return $this->status;
    }

    /**
     * @param UserStatus $status
     * @return static
     */
    public function setStatus(UserStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSuspendedUntil(): ?\DateTimeImmutable
    {
        return $this->suspendedUntil;
    }

    /**
     * @param \DateTimeImmutable|null $suspendedUntil
     * @return static
     */
    public function setSuspendedUntil(?\DateTimeImmutable $suspendedUntil): static
    {
        $this->suspendedUntil = $suspendedUntil;

        return $this;
    }

    /**
     * Vérifie si le compte utilisateur est activé.
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /**
     * Vérifie si l'utilisateur est banni.
     *
     * @return boolean
     */
    public function isBanned(): bool
    {
        return $this->status === UserStatus::Banned;
    }

    /**
     * Vérifie si l'utilisateur est suspendu.
     *
     * @return boolean
     */
    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended
            && $this->suspendedUntil !== null
            && $this->suspendedUntil > new \DateTimeImmutable()
        ;
    }

    /**
     * Retourne si le compte utilisateur est bloqué.
     *
     * @return boolean
     */
    public function isBlocked(): bool
    {
        return $this->isBanned() || $this->isSuspended();
    }

    /**
     * @return string|null
     */
    public function getAdminNote(): ?string
    {
        return $this->adminNote;
    }

    /**
     * @param string|null $adminNote
     * @return static
     */
    public function setAdminNote(?string $adminNote): static
    {
        $this->adminNote = $adminNote;

        return $this;
    }

    /**
     * @return boolean|null
     */
    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    /**
     * @param boolean $isVerified
     * @return static
     */
    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTokenRegister(): ?string
    {
        return $this->tokenRegister;
    }

    /**
     * @param string|null $tokenRegister
     * @return static
     */
    public function setTokenRegister(?string $tokenRegister): static
    {
        $this->tokenRegister = $tokenRegister;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTokenRegisterLifetime(): ?\DateTimeImmutable
    {
        return $this->tokenRegisterLifetime;
    }

    /**
     * @param \DateTimeImmutable $tokenRegisterLifetime
     * @return static
     */
    public function setTokenRegisterLifetime(\DateTimeImmutable $tokenRegisterLifetime): static
    {
        $this->tokenRegisterLifetime = $tokenRegisterLifetime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTokenForgotPassword(): ?string
    {
        return $this->tokenForgotPassword;
    }

    /**
     * @param string|null $tokenForgotPassword
     * @return static
     */
    public function setTokenForgotPassword(?string $tokenForgotPassword): static
    {
        $this->tokenForgotPassword = $tokenForgotPassword;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTokenForgotPasswordExpiredAt(): ?\DateTimeImmutable
    {
        return $this->tokenForgotPasswordExpiredAt;
    }

    /**
     * @param \DateTimeImmutable|null $tokenForgotPasswordExpiredAt
     * @return static
     */
    public function setTokenForgotPasswordExpiredAt(?\DateTimeImmutable $tokenForgotPasswordExpiredAt): static
    {
        $this->tokenForgotPasswordExpiredAt = $tokenForgotPasswordExpiredAt;

        return $this;
    }

    /**
     * Vérifie la validité du token de réinitialisation de mot de passe.
     *
     * @return boolean
     */
    public function isForgotPasswordTokenValid(): bool
    {
        return $this->tokenForgotPassword != null
            && $this->tokenForgotPasswordExpiredAt !== null
            && $this->tokenForgotPasswordExpiredAt > new \DateTimeImmutable()
        ;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviewsMade(): Collection
    {
        return $this->reviewsMade;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviewsReceived(): Collection
    {
        return $this->reviewsReceived;
    }

    /**
     * @param Review $review
     * @return static
     */
    public function addReviewMade(Review $review): static
    {
        if (!$this->reviewsMade->contains($review)) {
            $this->reviewsMade->add($review);
            $review->setReviewer($this);
        }
        return $this;
    }

    /**
     * @param Review $review
     * @return static
     */
    public function addReviewReceived(Review $review): static
    {
        if (!$this->reviewsReceived->contains($review)) {
            $this->reviewsReceived->add($review);
            $review->setReviewed($this);
        }
        return $this;
    }
}
