<?php

namespace App\Controller\Profile;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\User;
use App\Enum\TripStatus;
use App\Form\TripType;
use App\Service\BookingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile/trip', name: 'app_profile_trip')]
final class TripController extends AbstractController
{
    public function __construct(
        private BookingService $bookingService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: '')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('profile/trip/index.html.twig', [
            'trips' => $user->getTripsAsDriver()
        ]);
    }

    #[Route('/form/{trip}', name: '_form', defaults: ['trip' => null])]
    public function form(?Trip $trip, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if ($user->getVehicles()->isEmpty()) {
            $this->addFlash(
                'warning',
                'Vous devez ajouter un véhicule avant de pouvoir publier un trajet.'
            );
            return $this->redirectToRoute('app_profile_vehicle_form');
        }
        if (!$trip) {
            $trip = new Trip();
            $trip->setDriver($user);
        } else {
            if ($trip->getDriver()->getId() !== $user->getId()) {
                return $this->redirectToRoute('app_profile_trip');
            }
            if ($trip->getStatus() === TripStatus::Cancelled || $trip->getStatus() === TripStatus::Full) {
                $this->addFlash('danger', 'Impossible de modifier un trajet annulé ou complet.');
                return $this->redirectToRoute('app_profile_trip');
            }
        }
        $form = $this->createForm(TripType::class, $trip, [
            'vehicles' => $user->getVehicles()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($trip);
            $this->entityManager->flush();
            $this->addFlash('success', 'Trajet sauvegardé.');
            return $this->redirectToRoute('app_profile_trip');
        }
        return $this->render('profile/trip/form.html.twig', [
            'form' => $form,
            'title' => $trip->getId() ? 'Modifier le trajet' : 'Publier un trajet',
            'trip' => $trip
        ]);
    }

    #[Route('/cancel/{trip}', name: '_cancel', methods: ['POST'])]
    public function cancel(Trip $trip, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if (
            !$this->isCsrfTokenValid(
                'cancel_trip_' . $trip->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Problème inconnu.');
            return $this->redirectToRoute('app_profile_trip');
        }
        if ($trip->getDriver()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_trip');
        }
        try {
            $refundErrors = $this->bookingService->cancelTrip($trip);
            foreach ($refundErrors as $error) {
                $this->addFlash('warning', $error);
            }
            $this->addFlash('success', 'Trajet annulé. Les passagers ont été notifiés.');
        } catch (\LogicException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
        return $this->redirectToRoute('app_profile_trip');
    }

    #[Route('/bookings/{booking}/confirm', name: '_booking_confirm', methods: ['POST'])]
    public function confirmBooking(Booking $booking, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $trip = $booking->getTrip();
        if ($trip->getDriver()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        if (
            !$this->isCsrfTokenValid(
                'confirm_booking_' . $booking->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Problème inconnu.');
            return $this->redirectToRoute('app_profile_trip_show', ['trip' => $trip->getId()]);
        }
        try {
            $this->bookingService->confirm($booking);
            $this->addFlash('success', 'Réservation confirmée.');
        } catch (\LogicException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
        return $this->redirectToRoute('app_profile_trip_show', ['trip' => $trip->getId()]);
    }

    #[Route('/{trip}', name: '_show')]
    public function show(Trip $trip): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if ($trip->getDriver()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_trip');
        }
        return $this->render('profile/trip/show.html.twig', [
            'trip' => $trip
        ]);
    }
}
