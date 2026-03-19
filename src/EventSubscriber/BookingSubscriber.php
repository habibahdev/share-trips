<?php

namespace App\EventSubscriber;

use App\Entity\Booking;
use App\Enum\BookingStatus;
use App\Enum\TripStatus;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;

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
        $om = $args->getObjectManager();
        $this->updateTripSeats($entity, $om);
    }

    /**
     * @param LifecycleEventArgs<ObjectManager> $args
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

    private function updateTripSeats(Booking $booking, ObjectManager $entityManager): void
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
        } elseif ($trip->getStatus() === TripStatus::Full) {
            $trip->setStatus(TripStatus::Open);
        }
    }
}
