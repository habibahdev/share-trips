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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Vehicle> */
    #[ORM\OneToMany(mappedBy: 'usser', targetEntity: Vehicle::class, orphanRemoval: true)]
    private Collection $vehicles;

    /** @var Collection<int, Trip> */
    #[ORM\OneToMany(mappedBy: 'driver', targetEntity: Trip::class)]
    private Collection $tripsAsDriver;

    /** @var Collection<int, Booking> */
    #[ORM\OneToMany(mappedBy: 'passenger', targetEntity: Booking::class)]
    private Collection $bookings;

    /** @var Collection<int, Report> */
    #[ORM\OneToMany(mappedBy: 'reporter', targetEntity: Report::class)]
    private Collection $reportsMade;

    /** @var Collection<int, Report> */
    #[ORM\OneToMany(mappedBy: 'reported', targetEntity: Report::class)]
    private Collection $reportsReceived;

    #[ORM\Column(enumType: UserStatus::class)]
    private UserStatus $status = UserStatus::Active;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $suspendedUntil = null;

    #[ORM\Column(nullable: true, type: 'text')]
    private ?string $adminNote = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tokenRegister = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $tokenRegisterLifetime = null;

    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->tripsAsDriver = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->reportsMade = new ArrayCollection();
        $this->reportsReceived = new ArrayCollection();
        $this->isVerified = false;
        $this->tokenRegisterLifetime = (new \DateTimeImmutable('now'))->add(new DateInterval('P1D'));
    }

    public function __toString()
    {
        return $this->firstName . ' ' . $this->lastName;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
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

    /** @return Collection<int, Vehicle> */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): static
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
            $vehicle->setUsser($this);
        }
        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): static
    {
        if ($this->vehicles->removeElement($vehicle)) {
            if ($vehicle->getUsser() === $this) {
                $vehicle->setUsser(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Trip> */
    public function getTripsAsDriver(): Collection
    {
        return $this->tripsAsDriver;
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
            $booking->setPassenger($this);
        }
        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            if ($booking->getPassenger() === $this) {
                $booking->setPassenger(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Report> */
    public function getReportsMade(): Collection
    {
        return $this->reportsMade;
    }

    /** @return Collection<int, Report> */
    public function getReportsReceived(): Collection
    {
        return $this->reportsReceived;
    }

    public function getStatus(): ?UserStatus
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSuspendedUntil(): ?\DateTimeImmutable
    {
        return $this->suspendedUntil;
    }

    public function setSuspendedUntil(?\DateTimeImmutable $suspendedUntil): static
    {
        $this->suspendedUntil = $suspendedUntil;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isBanned(): bool
    {
        return $this->status === UserStatus::Banned;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended
            && $this->suspendedUntil !== null
            && $this->suspendedUntil > new \DateTimeImmutable()
        ;
    }

    public function isBlocked(): bool
    {
        return $this->isBanned() || $this->isSuspended();
    }

    public function getAdminNote(): ?string
    {
        return $this->adminNote;
    }

    public function setAdminNote(?string $adminNote): static
    {
        $this->adminNote = $adminNote;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getTokenRegister(): ?string
    {
        return $this->tokenRegister;
    }

    public function setTokenRegister(?string $tokenRegister): static
    {
        $this->tokenRegister = $tokenRegister;

        return $this;
    }

    public function getTokenRegisterLifetime(): ?\DateTimeImmutable
    {
        return $this->tokenRegisterLifetime;
    }

    public function setTokenRegisterLifetime(\DateTimeImmutable $tokenRegisterLifetime): static
    {
        $this->tokenRegisterLifetime = $tokenRegisterLifetime;

        return $this;
    }
}
