<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Trip;
use App\Entity\User;
use App\Form\BookingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingController extends AbstractController
{
    #[Route('/trip/{trip}/booking/add', name: 'app_booking_add')]
    public function index(Trip $trip, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $booking = new Booking();
        $booking->setPassenger($user);
        $booking->setTrip($trip);
        $form = $this->createForm(BookingType::class, $booking, [
            'available_seats' => $trip->getAvailableSeats()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($booking);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation confirmée.');
            return $this->redirectToRoute('app_trip_show', ['id' => $trip->getId()]);
        }
        return $this->render('booking/index.html.twig', [
            'trip' => $trip,
            'form' => $form
        ]);
    }
}
