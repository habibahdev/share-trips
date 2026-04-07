<?php

namespace App\Entity;

use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    /**
     * Identifiant du paiement.
     *
     * @var integer|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Montant du paiement.
     *
     * @var float|null
     */
    #[ORM\Column]
    private ?float $amount = null;

    /**
     * Statut du paiement.
     *
     * @var PaymentStatus
     */
    #[ORM\Column(enumType: PaymentStatus::class)]
    private PaymentStatus $status;

    /**
     * Date de création du paiement.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Date de dernière mise à jour du paiement.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Méthode de paiement.
     *
     * @var PaymentMethod|null
     */
    #[ORM\Column(enumType: PaymentMethod::class)]
    private ?PaymentMethod $method = null;

    /**
     * Réservation associée au paiement.
     *
     * @var Booking|null
     */
    #[ORM\OneToOne(targetEntity: Booking::class, inversedBy: 'payment')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Booking $booking = null;

    /**
     * Utilisateur ayant effectué le paiement.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $payer = null;

    public function __construct()
    {
        $this->status = PaymentStatus::Pending;
    }

    /**
     * Représentation textuelle du paiement.
     *
     * @return string
     */
    public function __toString(): string
    {
        $methodLabel = $this->method?->label() ?? 'N/A';
        return 'Paiement : ' . $methodLabel;
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
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return static
     */
    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return PaymentStatus
     */
    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    /**
     * @param PaymentStatus $status
     * @return static
     */
    public function setStatus(PaymentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Marque le paiement comme terminé.
     *
     * @return void
     */
    public function markAsCompleted(): void
    {
        $this->status = PaymentStatus::Completed;
    }

    /**
     * Marque le paiement commen échoué.
     *
     * @return void
     */
    public function markAsFailed(): void
    {
        $this->status = PaymentStatus::Failed;
    }

    /**
     * Remboursement du paiement.
     *
     * @return void
     */
    public function refund(): void
    {
        $this->status = PaymentStatus::Refunded;
    }

    /**
     * Vérifie que le paiement a été effectué avec succès.
     *
     * @return boolean
     */
    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
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
     * @return PaymentMethod|null
     */
    public function getMethod(): ?PaymentMethod
    {
        return $this->method;
    }

    /**
     * @param PaymentMethod|null $method
     * @return static
     */
    public function setMethod(?PaymentMethod $method): static
    {
        $this->method = $method;
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

    /**
     * @return User|null
     */
    public function getPayer(): ?User
    {
        return $this->payer;
    }

    /**
     * @param User|null $payer
     * @return static
     */
    public function setPayer(?User $payer): static
    {
        $this->payer = $payer;
        return $this;
    }
}
