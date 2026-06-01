<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Compte les messages non lus pour un utilisateur donné dans toutes ses conversations.
     *
     * @param User $user
     * @return integer
     */
    public function countUnreadForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.conversation', 'c')
            ->where('m.isRead = false')
            ->andWhere('m.sender != :user')
            ->andWhere('c.driver = :user OR c.passenger = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * COmpte les messages non lus dans une conversation précise pour un utilisateur donné.
     *
     * @param Conversation $conversation
     * @param User $user
     * @return integer
     */
    public function countUnreadInConversation(Conversation $conversation, User $user): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.conversation = :conv')
            ->andWhere('m.sender != :user')
            ->andWhere('m.isRead = false')
            ->setParameter('conv', $conversation)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Récupère les N derniers messages d'une conversation
     *
     * @param Conversation $conversation
     * @param integer $limit
     * @return Message[]
     */
    public function findLastMessages(Conversation $conversation, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->addSelect('s')
            ->where('m.conversation = :conv')
            ->setParameter('conv', $conversation)
            ->orderBy('m.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Marque comme lus tous les messages reçus par un utilisateur dans une conversation donnée.
     * Retourne le nombre de message mis à jour
     *
     * @param Conversation $conversation
     * @param User $reader
     * @return integer
     */
    public function markAsReadInConversation(Conversation $conversation, User $reader): int
    {
        return (int) $this->createQueryBuilder('m')
            ->update()
            ->set('m.isRead', ':true')
            ->where('m.conversation = :conv')
            ->andWhere('m.sender != :reader')
            ->andWhere('m.isRead = false')
            ->setParameter('true', true)
            ->setParameter('conv', $conversation)
            ->setParameter('reader', $reader)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Récupère tous les messages d'une conversation avec le sender chargé,
     * triè par date croissante.
     *
     * @param Conversation $conversation
     * @return Message[]
     */
    public function findByConversationWithSender(Conversation $conversation): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->addSelect('s')
            ->where('m.conversation = :conv')
            ->setParameter('conv', $conversation)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupère les message d'une conversation plus récents
     *
     * @param Conversation $conversation
     * @param integer $lastId
     * @return Message[]
     */
    public function findNewerThan(Conversation $conversation, int $lastId): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->addSelect('s')
            ->where('m.conversation = :conv')
            ->andWhere('m.id > :lastId')
            ->setParameter('conv', $conversation)
            ->setParameter('lastId', $lastId)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Vérifie s'il existe au moins un message non lu pour un utilisateur dans une conversation donnée.
     *
     * @param Conversation $conversation
     * @param User $user
     * @return boolean
     */
    public function hasUnreadInConversation(Conversation $conversation, User $user): bool
    {
        return $this->countUnreadInConversation($conversation, $user) > 0;
    }
}
