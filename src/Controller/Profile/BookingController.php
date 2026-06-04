<?php

namespace App\Controller\Profile;

use App\Entity\Booking;
use App\Entity\User;
use App\Repository\BookingRepository;
use App\Repository\TripRepository;
use App\Service\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile/booking', name: 'app_profile_booking')]
final class BookingController extends AbstractController
{
    public function __construct(private BookingService $bookingService)
    {
    }

    #[Route('', name: '')]
    public function index(
        BookingRepository $bookingRepository,
        TripRepository $tripRepository,
        Request $request
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
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
        return $this->render('profile/booking/index.html.twig', [
            'bookings' => $bookingRepository->findByPassenger($user),
            'availableTrips' => $availableTrips,
            'origin' => $origin,
            'destination' => $destination,
            'date' => $date
        ]);
    }

    #[Route('/{booking}', name: '_show')]
    public function show(Booking $booking): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_booking');
        }
        return $this->render('profile/booking/show.html.twig', [
            'booking' => $booking
        ]);
    }

    #[Route('/{booking}/cancel', name: '_cancel', methods: ['POST'])]
    public function cancel(Booking $booking, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if (
            !$this->isCsrfTokenValid(
                'cancel_booking_' . $booking->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Problème inconnu.');
            return $this->redirectToRoute('app_profile_booking');
        }
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_booking');
        }
        try {
            $this->bookingService->cancelByPassenger($booking);
            $this->addFlash('success', 'Réservation annulée.');
        } catch (\LogicException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
        return $this->redirectToRoute('app_profile_booking');
    }
}
