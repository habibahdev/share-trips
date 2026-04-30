<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Payment;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Form\BookingType;
use App\Repository\TripRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur responsable de la gestion des réservations.
 */
final class BookingController extends AbstractController
{
    /**
     * Réserver un trajet.
     *
     * @param integer $tripId Identifiant du trajet à réserver
     * @param TripRepository $tripRepository Repository des trajets
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Doctrine
     * @return Response
     */
    #[Route('/booking/add/{tripId}', name: 'app_booking_add')]
    public function add(
        int $tripId,
        TripRepository $tripRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        StripeService $stripe
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        $trip = $tripRepository->find($tripId);
        if (!$trip) {
            return $this->redirectToRoute('app_home');
        }
        if (!$user->isVerified()) {
            $this->addFlash(
                'warning',
                'Vous devez vérifier votre adresse e-mail avant de pouvoir réserver un trajet.'
            );
            return $this->redirectToRoute('app_trip_show', ['id' => $tripId]);
        }
        if ($trip->getDriver()->getId() === $user->getId()) {
            $this->addFlash('danger', 'Vous ne pouvez pas réserver votre propre trajet.');
            return $this->redirectToRoute('app_trip_show', ['id' => $tripId]);
        }
        if ($trip->isFull() || $trip->getAvailableSeats() <= 0) {
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
            $payment = new Payment();
            $payment->setPayer($user);
            $payment->setAmount($booking->getTotalPrice());
            $payment->setBooking($booking);
            $booking->setPayment($payment);
            $entityManager->persist($booking);
            $entityManager->persist($payment);
            $entityManager->flush();
            $session = $stripe->createCheckoutSession($booking, $payment);
            $payment->setStripeSessionId($session->id);
            $entityManager->flush();
            return $this->redirect($session->url);
        }
        return $this->render('booking/index.html.twig', [
            'trip' => $trip,
            'form' => $form
        ]);
    }

    /**
     * Undocumented function
     *
     * @param Booking $booking
     * @return Response
     */
    #[Route('/booking/{booking}/stripe/success', name: 'app_booking_stripe_success')]
    public function stripeSuccess(Booking $booking): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_home');
        }
        return $this->render('booking/success.html.twig', [
            'booking' => $booking
        ]);
    }

    /**
     * Undocumented function
     *
     * @param Booking $booking
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/booking/{booking}/stripe/cancel', name: 'app_booking_stripe_cancel')]
    public function stripeCancel(Booking $booking, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_home');
        }
        $payment = $booking->getPayment();
        if ($payment && !$payment->getStatus()->isFinal()) {
            $payment->markAsFailed();
            $booking->setStatus(BookingStatus::Cancelled);
            $entityManager->flush();
        }
        $this->addFlash('warning', 'Paiement annulé. Votre réservation n\'a pas été confirmée.');
        return $this->redirectToRoute('app_trip_show', [
            'id' => $booking->getTrip()->getId()
        ]);
    }

    #[Route('/booking/{booking}/success', name: 'app_booking_add_success')]
    public function success(Booking $booking): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_home');
        }
        return $this->render('booking/success.html.twig', [
            'booking' => $booking,
            'trip' => $booking->getTrip(),
        ]);
    }
}
