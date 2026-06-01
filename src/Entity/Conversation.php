<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'conversationsAsDriver')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $driver = null;

    #[ORM\ManyToOne(inversedBy: 'conversationsAsPassenger')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $passenger = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trip $trip = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(
        mappedBy: 'conversation',
        targetEntity: Message::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['sentAt' => 'ASC'])]
    private Collection $messages;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getPassenger(): ?User
    {
        return $this->passenger;
    }

    public function setPassenger(?User $passenger): static
    {
        $this->passenger = $passenger;
        return $this;
    }

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function setTrip(?Trip $trip): static
    {
        $this->trip = $trip;
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Retourne le dernier message de la conversation, ou null si vide.
     */
    public function getLastMessage(): ?Message
    {
        if ($this->messages->isEmpty()) {
            return null;
        }
        return $this->messages->last();
    }

    /**
     * Compte les messages non lus pour un utilisateur donné.
     */
    public function countUnreadFor(User $user): int
    {
        return $this->messages->filter(
            fn(Message $m) => !$m->isRead() && $m->getSender()?->getId() !== $user->getId()
        )->count();
    }

    /**
     * Retourne l'interlocuteur vu depuis un utilisateur donné.
     */
    public function getOtherParticipant(User $user): ?User
    {
        if ($this->driver?->getId() === $user->getId()) {
            return $this->passenger;
        }
        return $this->driver;
    }
}
