<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\User;
use App\Enum\BookingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /**
     * @param User $passenger
     * @return Booking[]
     */
    public function findByPassenger(User $passenger): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.trip', 't')->addSelect('t')
            ->leftJoin('t.driver', 'd')->addSelect('d')
            ->leftJoin('t.vehicle', 'v')->addSelect('v')
            ->where('b.passenger = :passenger')
            ->setParameter('passenger', $passenger)
            ->orderBy('t.departureAt', 'desc')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Trip $trip
     * @return Booking[]
     */
    public function findConfirmedTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.passenger', 'p')->addSelect('p')
            ->where('b.trip = :trip')
            ->andWhere('b.status = :status')
            ->setParameter('trip', $trip)
            ->setParameter('status', BookingStatus::Confirmed)
            ->orderBy('b.createdAt', 'asc')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Trip $trip
     * @param integer|null $excludeBookingId
     * @return integer
     */
    public function countConfirmedSeats(Trip $trip, ?int $excludeBookingId = null): int
    {
        $query = $this->createQueryBuilder('b')
            ->select('sum(b.seatsBooked)')
            ->where('b.trip = :trip')
            ->andWhere('b.status = :status')
            ->setParameter('trip', $trip)
            ->setParameter('status', BookingStatus::Confirmed);

        if ($excludeBookingId !== null) {
            $query->andWhere('b.id != :id')
               ->setParameter('id', $excludeBookingId);
        }
        return (int) ($query->getQuery()->getSingleScalarResult() ?? 0);
    }

    /**
     * @param Trip $trip
     * @param User $passenger
     * @return boolean
     */
    public function hasActiveBooking(Trip $trip, User $passenger): bool
    {
        $count = $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->where('b.trip = :trip')
            ->andWhere('b.passenger = :passenger')
            ->andWhere('b.status != :cancelled')
            ->setParameter('trip', $trip)
            ->setParameter('passenger', $passenger)
            ->setParameter('cancelled', BookingStatus::Cancelled)
            ->getQuery()
            ->getSingleScalarResult();
        return (int) $count > 0;
    }
}
