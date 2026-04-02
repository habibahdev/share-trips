<?php

namespace App\Controller\Profile;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Enum\TripStatus;
use App\Form\TripType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TripController extends AbstractController
{
    #[Route('/profile/trip', name: 'app_profile_trip')]
    public function index(): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        return $this->render('profile/trip/index.html.twig', [
            'trips' => $user->getTripsAsDriver()
        ]);
    }

    #[Route('/profile/trip/form/{trip}', name: 'app_profile_trip_form', defaults: ['trip' => null])]
    public function form(?Trip $trip, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
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
            $entityManager->persist($trip);
            $entityManager->flush();
            $this->addFlash('success', 'Trajet sauvegardé.');
            return $this->redirectToRoute('app_profile_trip');
        }
        return $this->render('profile/trip/form.html.twig', [
            'form' => $form,
            'title' => $trip->getId() ? 'Modifier le trajet' : 'Publier un trajet',
            'trip' => $trip
        ]);
    }

    #[Route('/profile/trip/cancel/{trip}', name: 'app_profile_trip_cancel', methods: ['POST'])]
    public function cancel(
        Trip $trip,
        Request $request,
        EntityManagerInterface $entityManager,
        MailService $mailer
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
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
        if ($trip->getStatus() === TripStatus::Cancelled) {
            return $this->redirectToRoute('app_profile_trip');
        }
        if ($trip->getDepartureAt() < new \DateTimeImmutable()) {
            $this->addFlash('danger', 'Impossible d\'annuler un trajet déjà effectué.');
            return $this->redirectToRoute('app_profile_trip');
        }
        $trip->setStatus(TripStatus::Cancelled);
        $entityManager->flush();
        foreach ($trip->getBookings() as $booking) {
            if ($booking->getStatus() !== BookingStatus::Cancelled) {
                $mailer->sendTripCancellationToPassanger($booking);
                if ($booking->getStatus() === BookingStatus::Confirmed) {
                    $mailer->sendRefund($booking);
                }
                $booking->setStatus(BookingStatus::Cancelled);
            }
        }
        $this->addFlash('success', 'Trajet annulé. Les passagers ont été notifiés.');
        return $this->redirectToRoute('app_profile_trip');
    }

    #[Route('/profile/trip/bookings/{booking}/confirm', name: 'app_profile_trip_booking_confirm', methods: ['POST'])]
    public function confirmBooking(
        Booking $booking,
        EntityManagerInterface $entityManager,
        Request $request,
        MailService $mailer
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
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
        if ($booking->getStatus() === BookingStatus::Confirmed) {
            $this->addFlash('warning', 'Cette réservation est déjà confirmée.');
            return $this->redirectToRoute('app_profile_trip_show', ['trip' => $trip->getId()]);
        }
        $booking->setStatus(BookingStatus::Confirmed);
        $confirmedSeats = 0;
        foreach ($trip->getBookings() as $b) {
            if ($b->getStatus() === BookingStatus::Confirmed) {
                $confirmedSeats += $b->getSeatsBooked();
            }
        }
        $remaining = $trip->getVehicle()->getSeats() - $confirmedSeats;
        $trip->setAvailableSeats(max(0, $remaining));
        $trip->setStatus($remaining <= 0 ? TripStatus::Full : TripStatus::Open);
        $entityManager->flush();
        $mailer->sendBookingApproved($booking);
        $this->addFlash('success', 'Réservation confirmée.');
        return $this->redirectToRoute('app_profile_trip_show', ['trip' => $trip->getId()]);
    }

    #[Route('/profile/trip/{trip}', name: 'app_profile_trip_show')]
    public function show(Trip $trip): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($trip->getDriver()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_trip');
        }
        return $this->render('profile/trip/show.html.twig', [
            'trip' => $trip
        ]);
    }
}
