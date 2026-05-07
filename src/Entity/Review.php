<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['booking', 'reviewer'])]
class Review
{
    /**
     * Identifiant de l'avis.
     *
     * @var integer|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Utilisateur qui donne l'avis.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reviewsMade')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reviewer = null;

    /**
     * Utilisateur évalué.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reviewsReceived')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reviewed = null;

    /**
     * Réservation associé à l'avis.
     *
     * @var Booking|null
     */
    #[ORM\ManyToOne(targetEntity: Booking::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Booking $booking = null;

    /**
     * Note attribuée (de 1 à 5).
     *
     * @var integer|null
     */
    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être comprise entre 1 et 5.')]
    private ?int $rating = null;

    /**
     * Commentaire de l'avis.
     */
    #[ORM\Column(Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500, maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $comment = null;

    /**
     * Date de création de l'avis.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Représentation textuelle de l'avis
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            'Avis de %s sur %s (%d/5)',
            $this->reviewer?->getFullName(),
            $this->reviewed?->getFullName(),
            $this->rating ?? 0
        );
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
    public function getReviewer(): ?User
    {
        return $this->reviewer;
    }

    /**
     * @param User|null $reviewer
     * @return static
     */
    public function setReviewer(?User $reviewer): static
    {
        $this->reviewer = $reviewer;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getReviewed(): ?User
    {
        return $this->reviewed;
    }

    /**
     * @param User|null $reviewed
     * @return static
     */
    public function setReviewed(?User $reviewed): static
    {
        $this->reviewed = $reviewed;
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
     * @return integer|null
     */
    public function getRating(): ?int
    {
        return $this->rating;
    }

    /**
     * @param integer|null $rating
     * @return static
     */
    public function setRating(?int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return static
     */
    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
