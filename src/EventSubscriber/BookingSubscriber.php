<?php

namespace App\EventSubscriber;

use App\Entity\Booking;
use App\Enum\BookingStatus;
use App\Enum\TripStatus;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class BookingSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::postPersist
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Booking) {
            return;
        }
        if (!$args->hasChangedField('status')) {
            return;
        }
        /** @var EntityManagerInterface $em */
        $em = $args->getObjectManager();
        $this->updateTripSeats($entity, $em);
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Booking) {
            return;
        }
        if ($entity->getStatus() !== BookingStatus::Confirmed) {
            return;
        }
        $this->updateTripSeats($entity, $args->getObjectManager());
    }

    private function updateTripSeats(Booking $booking, EntityManagerInterface $entityManager): void
    {
        $trip = $booking->getTrip();
        if (!$trip) {
            return;
        }
        /** @var \Doctrine\ORM\EntityRepository<Booking> $repo */
        $repo = $entityManager->getRepository(Booking::class);
        $takenSeats = (int) $repo->createQueryBuilder('b')
            ->select('COALESCE(SUM(b.seatsBooked),0)')
            ->where('b.trip = :trip')
            ->andWhere('b.status = :status')
            ->setParameter('trip', $trip)
            ->setParameter('status', BookingStatus::Confirmed)
            ->getQuery()
            ->getSingleScalarResult()
        ;
        $totalSeats = $trip->getVehicle()->getSeats();
        $availableSeats = max(0, $totalSeats - $takenSeats);
        $trip->setAvailableSeats($availableSeats);
        if ($availableSeats <= 0) {
            $trip->setStatus(TripStatus::Full);
        } elseif ($availableSeats > 0) {
            if (!in_array($trip->getStatus(), [TripStatus::Cancelled, TripStatus::Completed])) {
                $trip->setStatus(TripStatus::Open);
            }
        }

        $em = $entityManager;
        if (!$em->contains($trip)) {
            $em->persist($trip);
        }
        $uow = $em->getUnitOfWork();
        $meta = $em->getClassMetadata($trip::class);
        $uow->recomputeSingleEntityChangeSet($meta, $trip);
    }
}
