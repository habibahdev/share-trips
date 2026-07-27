<?php

namespace App\Controller\Profile;

use App\Controller\AbstractAppController;
use App\Dto\TripSearchCriteria;
use App\Entity\Booking;
use App\Repository\BookingRepository;
use App\Repository\TripRepository;
use App\Security\BookingVoter;
use App\Service\BookingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile/booking', name: 'app_profile_booking')]
final class BookingController extends AbstractAppController
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
        $user = $this->getAppUser();
        $criteria = TripSearchCriteria::fromRequest($request);
        $availableTrips = null;
        if ($criteria->origin || $criteria->destination || $criteria->date) {
            $availableTrips = $tripRepository->findAvailableTrips(
                $criteria->origin,
                $criteria->destination,
                $criteria->date
            );
        }
        return $this->render('profile/booking/index.html.twig', [
            'bookings' => $bookingRepository->findByPassenger($user),
            'availableTrips' => $availableTrips,
            'origin' => $criteria->origin,
            'destination' => $criteria->destination,
            'date' => $criteria->date
        ]);
    }

    #[Route('/{booking}', name: '_show')]
    public function show(Booking $booking): Response
    {
        $this->getAppUser();
        $this->denyAccessUnlessGranted(BookingVoter::VIEW, $booking);
        return $this->render('profile/booking/show.html.twig', [
            'booking' => $booking
        ]);
    }

    #[Route('/{booking}/cancel', name: '_cancel', methods: ['POST'])]
    public function cancel(Booking $booking, Request $request): Response
    {
        $this->getAppUser();
        $this->denyAccessUnlessGranted(BookingVoter::CANCEL, $booking);
        if (
            !$this->isCsrfTokenValid(
                'cancel_booking_' . $booking->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Problème inconnu.');
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
