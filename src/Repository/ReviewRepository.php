<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @param User $user
     * @return Review[]
     */
    public function findByReviewed(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.reviewer', 'u')->addSelect('u')
            ->leftJoin('r.booking', 'b')->addSelect('b')
            ->where('r.reviewed = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User $user
     * @return float|null
     */
    public function getAverageRating(User $user): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avg')
            ->where('r.reviewed = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
        return $result !== null ? round((float) $result, 1) : null;
    }

    /**
     * @param Booking $booking
     * @param User $reviewer
     * @return Review|null
     */
    public function findByBookingAndReviewer(Booking $booking, User $reviewer): ?Review
    {
        return $this->findOneBy([
            'booking' => $booking,
            'reviewer' => $reviewer
        ]);
    }

    /**
     * @param User $user
     * @return integer
     */
    public function countByReviewed(User $user): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.reviewed = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
