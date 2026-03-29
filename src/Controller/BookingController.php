<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Payment;
use App\Entity\User;
use App\Form\BookingType;
use App\Repository\TripRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingController extends AbstractController
{
    #[Route('/booking/add/{tripId}', name: 'app_booking_add')]
    public function index(
        int $tripId,
        TripRepository $tripRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        MailService $mailer
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        $trip = $tripRepository->findOneBy(['id' => $tripId]);
        if (!$trip) {
            return $this->redirectToRoute('app_home');
        }
        if ($trip->getDriver()->getId() === $user->getId()) {
            $this->addFlash('danger', 'Vous ne pouvez pas réserver vore propre trajet.');
            return $this->redirectToRoute('app_trip_show', ['id' => $tripId]);
        }
        if ($trip->getAvailableSeats() <= 0 || $trip->getStatus()->value === 'full') {
            $this->addFlash('danger', 'Ce trajet est complet.');
            return $this->redirectToRoute('app_trip_show', ['id' => $tripId]);
        }
        $booking = new Booking();
        $booking->setPassenger($user);
        $booking->setTrip($trip);
        $form = $this->createForm(BookingType::class, $booking, [
            'available_seats' => $trip->getAvailableSeats()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $method = $form->get('payment')->getData();
            $payment = new Payment();
            $payment->setPayer($user);
            $payment->setAmount($booking->getTotalPrice());
            $payment->setMethod($method);
            $payment->setBooking($booking);
            $booking->setPayment($payment);
            $entityManager->persist($booking);
            $entityManager->persist($payment);
            $entityManager->flush();
            $mailer->sendBookingConfirmation($booking);
            $mailer->sendNewBookingToDriver($booking);
            return $this->redirectToRoute('app_booking_add_success', [
                'tripId' => $trip->getId()
            ]);
        }
        return $this->render('booking/index.html.twig', [
            'trip' => $trip,
            'form' => $form
        ]);
    }

    #[Route('/booking/success/{tripId}', name: 'app_booking_add_success')]
    public function success(int $tripId, TripRepository $tripRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $trip = $tripRepository->findOneBy(['id' => $tripId]);
        if (!$trip) {
            return $this->redirectToRoute('app_home');
        }
        return $this->render('booking/success.html.twig', [
            'trip' => $trip
        ]);
    }
}
