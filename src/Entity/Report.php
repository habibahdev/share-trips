<?php

namespace App\Entity;

use App\Enum\ReportStatus;
use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Report
{
    /**
     * Identifiant du signalement.
     *
     * @var integer|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Utilisateur ayant crée le signalement.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reportsMade')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reporter = null;

    /**
     * Utilisateur signalé.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reportsReceived')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reported = null;

    /**
     * Réservation associée au signalement.
     *
     * @var Booking|null
     */
    #[ORM\ManyToOne(targetEntity: Booking::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Booking $booking = null;

    /**
     * Motif du signalement.
     *
     * @var string|null
     */
    #[ORM\Column(length: 150)]
    private ?string $reason = null;

    /**
     * Détails supplémentaires du signalement.
     *
     * @var string|null
     */
    #[ORM\Column(nullable: true, type: 'text')]
    private ?string $details = null;

    /**
     * Statut du signalement.
     *
     * @var ReportStatus
     */
    #[ORM\Column(enumType: ReportStatus::class)]
    private ReportStatus $status = ReportStatus::Pending;

    /**
     * Date de création du signalement.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Note de l'administrateur.
     *
     * @var string|null
     */
    #[ORM\Column(nullable: true, type: 'text')]
    private ?string $adminNote = null;

    /**
     * Représentation textuelle du signalement.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'Signalement : ' . $this->id;
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
    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    /**
     * @param User|null $reporter
     * @return static
     */
    public function setReporter(?User $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getReported(): ?User
    {
        return $this->reported;
    }

    /**
     * @param User|null $reported
     * @return static
     */
    public function setReported(?User $reported): static
    {
        $this->reported = $reported;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return static
     */
    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return ReportStatus|null
     */
    public function getStatus(): ?ReportStatus
    {
        return $this->status;
    }

    /**
     * @param ReportStatus $status
     * @return static
     */
    public function setStatus(ReportStatus $status): static
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
     * @return string|null
     */
    public function getDetails(): ?string
    {
        return $this->details;
    }

    /**
     * @param string|null $details
     * @return static
     */
    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return Booking|null
     */
    public function getBooking(): ?Booking
    {
        return $this->booking;
    }

    /**
     * @param Booking|null $booking
     * @return static
     */
    public function setBooking(?Booking $booking): static
    {
        $this->booking = $booking;

        return $this;
    }
}
