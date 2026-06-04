<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Trip;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Undocumented function
     *
     * @param User $user
     * @return Query<int, Conversation>
     */
    public function findAllForUserQuery(User $user): Query
    {
        $sql = $this->createQueryBuilder('c')
            ->leftJoin('c.driver', 'd')
            ->addSelect('d')
            ->leftJoin('c.passenger', 'p')
            ->addSelect('p')
            ->leftJoin('c.trip', 't')
            ->addSelect('t')
            ->leftJoin(
                'c.messages',
                'm',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'm.isRead = false AND m.sender != :user'
            )
            ->addSelect('COUNT(m.id) as HIDDEN unread_count')
            ->setParameter('user', $user)
            ->where('c.driver = :user OR c.passenger = :user')
            ->groupBy('c.id, d.id, p.id, t.id')
            ->orderBy('unread_count', 'DESC')
            ->addOrderBy('c.createdAt', 'DESC');

        /** @var Query<int, Conversation> $query */
        $query = $sql->getQuery();

        return $query;
    }

    /**
     * Trouve une conversation entre un passager et un trajet donné.
     *
     * @param Trip $trip
     * @param User $passenger
     * @return Conversation|null
     */
    public function findOneByTripAndPassenger(Trip $trip, User $passenger): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.trip = :trip')
            ->andWhere('c.passenger = :passenger')
            ->setParameter('trip', $trip)
            ->setParameter('passenger', $passenger)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Trouve une conversation par son id en chargeant tous les participants
     * et messages en une seule requête.
     *
     * @param integer $id
     * @return Conversation|null
     */
    public function findOneWithMessages(int $id): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->addSelect('m')
            ->leftJoin('m.sender', 'ms')
            ->addSelect('ms')
            ->leftJoin('c.driver', 'd')
            ->addSelect('d')
            ->leftJoin('c.passenger', 'p')
            ->addSelect('p')
            ->leftJoin('c.trip', 't')
            ->addSelect('t')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Compte le nombre total de messages non lus pour un utilisateur
     * dans toutes ses conversations.
     *
     * @param User $user
     * @return integer
     */
    public function countAllUnreadForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(m.id)')
            ->join('c.messages', 'm')
            ->where('c.driver = :user OR c.passenger = :user')
            ->andWhere('m.sender != :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Marque tous les messages d'une conversation comme lus
     * pour un utilisateur donnée
     *
     * @param Conversation $conversation
     * @param User $user
     * @return void
     */
    public function markAllAsReadFor(Conversation $conversation, User $user): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->update(\App\Entity\Message::class, 'm')
            ->set('m.isRead', ':true')
            ->where('m.conversation = :conv')
            ->andWhere('m.sender != :user')
            ->andWhere('m.isRead = false')
            ->setParameter('true', true)
            ->setParameter('conv', $conversation)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Trouve toutes les conversations liés à un trajet donnée.
     *
     * @param Trip $trip
     * @return Conversation[]
     */
    public function findByTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->addSelect('m')
            ->where('c.trip = :trip')
            ->setParameter('trip', $trip)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
