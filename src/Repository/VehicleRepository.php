<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    /**
     * @param User $user
     * @return Vehicle[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.brand', 'asc')
            ->addOrderBy('v.model', 'asc')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $licensePlate
     * @param integer|null $excludeId
     * @return boolean
     */
    public function isLicensePlateUsed(string $licensePlate, ?int $excludeId = null): bool
    {
        $query = $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->where('v.plate = :plate')
            ->setParameter('plate', $licensePlate)
        ;

        if ($excludeId !== null) {
            $query->andWhere('v.id != :id')
            ->setParameter('id', $excludeId);
        }
        return (int)$query->getQuery()->getSingleScalarResult() > 0;
    }
}
