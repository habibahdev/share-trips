<?php

namespace App\Controller\Profile;

use App\Entity\Booking;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Enum\TripStatus;
use App\Repository\BookingRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingController extends AbstractController
{
    #[Route('/profile/booking', name: 'app_profile_booking')]
    public function index(
        BookingRepository $bookingRepository,
        TripRepository $tripRepository,
        Request $request
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        $origin = $request->query->get('origin');
        $destination = $request->query->get('destination');
        $dateString = $request->query->get('date');
        $date = null;
        if ($dateString) {
            try {
                $date = new \DateTimeImmutable($dateString);
            } catch (\Exception) {
                $date = null;
            }
        }
        $availableTrips = null;
        if ($origin || $destination || $date) {
            $availableTrips = $tripRepository->findAvailableTrips(
                $origin,
                $destination,
                $date
            );
        }
        $bookings = $bookingRepository->findByPassenger($user);
        return $this->render('profile/booking/index.html.twig', [
            'bookings' => $bookings,
            'availableTrips' => $availableTrips,
            'origin' => $origin,
            'destination' => $destination,
            'date' => $date
        ]);
    }

    #[Route('/profile/booking/{booking}', name: 'app_profile_booking_show')]
    public function show(Booking $booking): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_booking');
        }
        return $this->render('profile/booking/show.html.twig', [
            'booking' => $booking
        ]);
    }

    #[Route('/profile/booking/{booking}/cancel', name: 'app_profile_booking_cancel', methods: ['POST'])]
    public function cancel(Booking $booking, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_booking');
        }
        if ($booking->getStatus() === BookingStatus::Cancelled) {
            return $this->redirectToRoute('app_profile_booking');
        }
        if ($booking->getTrip()->getDepartureAt() < new \DateTimeImmutable()) {
            $this->addFlash('danger', 'Impossible d\annuler un trajet déjà effectué.');
            return $this->redirectToRoute('app_profile_booking');
        }
        if ($booking->getStatus() === BookingStatus::Confirmed) {
            $trip = $booking->getTrip();
            $newAvailable = $trip->getAvailableSeats() + $booking->getSeatsBooked();
            $trip->setAvailableSeats(min($trip->getVehicle()->getSeats(), $newAvailable));
            if ($trip->getStatus() === TripStatus::Full) {
                $trip->setStatus(TripStatus::Open);
            }
        }
        $booking->setStatus(BookingStatus::Cancelled);
        $entityManager->flush();
        $this->addFlash('success', 'Réservation annulée.');
        return $this->redirectToRoute('app_profile_booking');
    }
}
