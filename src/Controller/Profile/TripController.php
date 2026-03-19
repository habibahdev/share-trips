<?php

namespace App\Controller\Profile;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Enum\TripStatus;
use App\Form\TripType;
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
        $trips = $user->getTripsAsDriver();
        return $this->render('profile/trip/index.html.twig', [
            'trips' => $trips,
        ]);
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

    #[Route('/profile/trip/form/{trip}', name: 'app_profile_trip_form', defaults: ['trip' => null])]
    public function form(?Trip $trip, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if (!$trip) {
            $trip = new Trip();
            $trip->setDriver($user);
        } elseif ($trip->getDriver()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_trip');
        }
        $form = $this->createForm(TripType::class, $trip, [
            'vehicles' => $user->getVehicles()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trip);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Informations trajet sauvegardée.'
            );
            return $this->redirectToRoute('app_profile_trip');
        }
        return $this->render('profile/trip/form.html.twig', [
            'form' => $form,
            'title' => $trip->getId() ? 'Modifier le trajet' : 'Publier un trajet',
            'trip' => $trip
        ]);
    }

    #[Route('/profile/trip/cancel/{trip}', name: 'app_profile_trip_cancel')]
    public function cancel(Trip $trip, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($trip->getDriver()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_trip');
        }
        $trip->setStatus(TripStatus::Cancelled);
        $entityManager->flush();
        $this->addFlash(
            'success',
            'Trajet annulé.'
        );
        return $this->redirectToRoute('app_profile_trip');
    }

    #[Route('/profile/trip/bookings/{booking}/confirm', name: 'app_profile_trip_booking_confirm')]
    public function confirmBooking(
        Booking $booking,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        $trip = $booking->getTrip();
        if ($trip->getDriver()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        $submittedToken = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('confirm_' . $booking->getId(), $submittedToken)) {
            throw $this->createAccessDeniedException('Un problème est survenu.');
        }
        $booking->setStatus(BookingStatus::Confirmed);
        $entityManager->flush();
        $this->addFlash('success', 'Réservation confirmée.');
        return $this->redirectToRoute('app_profile_trip_show', [
            'trip' => $trip->getId()
        ]);
    }
}
