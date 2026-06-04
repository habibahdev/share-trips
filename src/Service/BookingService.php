<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Enum\BookingStatus;
use App\Enum\TripStatus;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;

final class BookingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailService $mailer,
        private StripeService $stripe,
        private BookingRepository $bookingRepository
    ) {
    }

    /**
     * Confirme une réservation (action conducteur)
     * Met à jour les places disponibles et le statut du trajet
     *
     * @param Booking $booking
     * @throws \LogicException si le paiement n'est pas validé
     * @return void
     */
    public function confirm(Booking $booking): void
    {
        if (!$booking->getPayment()?->isSuccessful()) {
            throw new \LogicException('Impossible de confirmer une réservation sans paimeent valide.');
        }
        if ($booking->getStatus() === BookingStatus::Confirmed) {
            throw new \LogicException('Cette réservation est déjà confirmée.');
        }
        $booking->setStatus(BookingStatus::Confirmed);
        $this->recalculateAvailableSeats($booking->getTrip());
        $this->entityManager->flush();
        $this->mailer->sendBookingApproved($booking);
    }

    /**
     * Anuule une réservation (action passager)
     * Rembourse si le paiement était validé et libère les places.
     *
     * @param Booking $booking
     * @throws \LogicException si le trajet est déjà passé
     * @return void
     */
    public function cancelByPassenger(Booking $booking): void
    {
        if ($booking->getStatus() === BookingStatus::Cancelled) {
            throw new \LogicException('Cette réservation est déjà annulée.');
        }
        if ($booking->getTrip()->getDepartureAt() < new \DateTimeImmutable()) {
            throw new \LogicException('Impossible d\'annuler un trajet déjà effectué.');
        }
        $wasConfirmed = $booking->getStatus() === BookingStatus::Confirmed;
        $booking->setStatus(BookingStatus::Cancelled);
        if ($wasConfirmed) {
            $this->recalculateAvailableSeats($booking->getTrip());
            if ($booking->getPayment()?->isSuccessful()) {
                $this->stripe->refund($booking->getPayment());
                $booking->getPayment()->refund();
                $this->mailer->sendRefund($booking);
            }
        }
        $this->entityManager->flush();
        if ($wasConfirmed) {
            $this->mailer->sendBookingCancellationToDriver($booking);
        }
    }

    /**
     * Annule un trajet entier (action conducteur)
     * Annule toutes les réservations actives, rembourse les paiements valides et notifie chaque passager
     *
     * @param Trip $trip
     * @throws \LogicException si le trajet est déjà passé ou annulé
     * @return array<string> tableau de messages d'erreur de remboursements échoués
     */
    public function cancelTrip(Trip $trip): array
    {
        if ($trip->getStatus() === TripStatus::Cancelled) {
            throw new \LogicException('Ce trajet est déjà annulé.');
        }
        if ($trip->getDepartureAt() < new \DateTimeImmutable()) {
            throw new \LogicException('Impossible d\'annuler un trajet déjà effectué.');
        }
        $trip->setStatus(TripStatus::Cancelled);
        $refundErrors = [];
        foreach ($trip->getBookings() as $booking) {
            if ($booking->getStatus() === BookingStatus::Cancelled) {
                continue;
            }
            $this->mailer->sendTripCancellationToPassenger($booking);
            $payment = $booking->getPayment();
            if ($payment?->isSuccessful()) {
                try {
                    $this->stripe->refund($payment);
                    $payment->refund();
                    $this->mailer->sendRefund($booking);
                } catch (\Exception $e) {
                    $refundErrors[] = sprintf(
                        'Remboursement échoué pour la réservation #%d.',
                        $booking->getId()
                    );
                }
            }
            $booking->setStatus(BookingStatus::Cancelled);
        }
        $this->entityManager->flush();
        return $refundErrors;
    }

    /**
     * Recalcule les places disponibles d'un trajet à partir des réservations confirmées rélles.
     * Met aussi à jour le statut Open/Full du trajet
     *
     * @param Trip $trip
     * @return void
     */
    private function recalculateAvailableSeats(Trip $trip): void
    {
        $confirmedSeats = $this->bookingRepository->countConfirmedSeats($trip);
        $remaining = max(0, $trip->getVehicle()->getSeats() - $confirmedSeats);
        $trip->setAvailableSeats($remaining);
        $trip->setStatus($remaining <= 0 ? TripStatus::Full : TripStatus::Open);
    }
}
