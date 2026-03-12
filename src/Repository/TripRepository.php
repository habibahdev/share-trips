<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Entity\User;
use App\Enum\TripStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trip>
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    /**
     * Undocumented function
     *
     * @param string|null $origin
     * @param string|null $destination
     * @param \DateTimeImmutable|null $date
     * @return Trip[]
     */
    public function findAvailableTrips(
        ?string $origin = null,
        ?string $destination = null,
        ?\DateTimeImmutable $date = null
    ): array {
        $query = $this->createAvailableTripsQueryBuilder();
        if ($origin) {
            $query->andWhere('lower(t.origin) like lower(:origin)')
                ->setParameter('origin', '%' . $origin . '%');
        }
        if ($destination) {
            $query->andWhere('lower(t.destination) like lower(:destination)')
                ->setParameter('destination', '%' . $destination . '%');
        }
        if ($date) {
            $query->andWhere('t.departureAt between :start and :end')
                ->setParameter('start', $date->setTime(0, 0, 0))
                ->setParameter('end', $date->setTime(23, 59, 59));
        }
        return $query->getQuery()->getResult();
    }

    /**
     * Undocumented function
     *
     * @param integer $limit
     * @return Trip[]
     */
    public function findNextAvailableTrips(int $limit = 6): array
    {
        return $this->createAvailableTripsQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Undocumented function
     *
     * @param User $driver
     * @return Trip[]
     */
    public function findByDriver(User $driver): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.vehicle', 'v')->addSelect('v')
            ->leftJoin('t.bookings', 'b')->addSelect('b')
            ->where('t.driver = :driver')
            ->setParameter('driver', $driver)
            ->orderBy('t.departureAt', 'desc')
            ->getQuery()
            ->getResult()
        ;
    }

    private function createAvailableTripsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.driver', 'd')->addSelect('d')
            ->leftJoin('t.vehicle', 'v')->addSelect('v')
            ->where('t.status = :status')
            ->andWhere('t.departureAt > :now')
            ->setParameter('status', TripStatus::Open)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('t.departureAt', 'asc')
        ;
    }
}
