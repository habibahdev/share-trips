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
     * @param MailService $mailer Service d'envoi d'emails
     * @return Response
     */
    #[Route('/booking/add/{tripId}', name: 'app_booking_add')]
    public function add(
        int $tripId,
        TripRepository $tripRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        MailService $mailer
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

    /**
     * Page de confirmation de la réservation.
     *
     * @param integer $tripId Identifiant du trajet
     * @param TripRepository $tripRepository Repository des trajets.
     * @return Response
     */
    #[Route('/booking/success/{tripId}', name: 'app_booking_add_success')]
    public function success(int $tripId, TripRepository $tripRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $trip = $tripRepository->find($tripId);
        if (!$trip) {
            return $this->redirectToRoute('app_home');
        }
        $hasBooking = false;
        foreach ($trip->getBookings() as $booking) {
            if ($booking->getPassenger()->getId() === $user->getId()) {
                $hasBooking = true;
                break;
            }
        }
        if (!$hasBooking) {
            return $this->redirectToRoute('app_home');
        }
        return $this->render('booking/success.html.twig', [
            'trip' => $trip
        ]);
    }

    /**
     * COnfirmation du paiement d'une réservation.
     *
     * @param Booking $booking Réservation concernée
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Doctrine
     * @param MailService $mailer Service d'envoi d'emails
     * @return Response
     */
    #[Route('/booking/{booking}/payment/confirm', name: 'app_booking_payment_confirm', methods: ['POST'])]
    public function confirmPayment(
        Booking $booking,
        Request $request,
        EntityManagerInterface $entityManager,
        MailService $mailer
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        if (
            !$this->isCsrfTokenValid(
                'confirm_payment_' . $booking->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Problème inconnu.');
            return $this->redirectToRoute('app_profile_booking_show', ['booking' => $booking->getId()]);
        }
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_booking');
        }
        $payment = $booking->getPayment();
        if (!$payment) {
            $this->addFlash('danger', 'Aucun paiement associé à cette réservation.');
            return $this->redirectToRoute('app_profile_booking_show', ['booking' => $booking->getId()]);
        }
        if ($payment->getStatus()->isFinal()) {
            $this->addFlash('warning', 'Ce paiement a déjà été traité.');
            return $this->redirectToRoute('app_profile_booking_show', ['booking' => $booking->getId()]);
        }
        $payment->markAsCompleted();
        $entityManager->flush();
        $mailer->sendPaymentConfirmation($booking);
        $this->addFlash('success', 'Paiement confirmé.');
        return $this->redirectToRoute('app_profile_booking_show', ['booking' => $booking->getId()]);
    }

    /**
     * Marque un paiement comme échoué.
     *
     * @param Booking $booking Réservation concernée
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Doctrine
     * @return Response
     */
    #[Route('/booking/{booking}/payment/fail', name: 'app_booking_payment_fail', methods: ['POST'])]
    public function failPayment(
        Booking $booking,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        if (
            !$this->isCsrfTokenValid(
                'fail_payment_' . $booking->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Problème inconnu.');
            return $this->redirectToRoute('app_profile_booking_show', ['booking' => $booking->getId()]);
        }
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_profile_booking');
        }
        $payment = $booking->getPayment();
        if (!$payment || $payment->getStatus()->isFinal()) {
            $this->addFlash('warning', 'Ce paiement a déjà été traité.');
            return $this->redirectToRoute('app_profile_booking_show', ['booking' => $booking->getId()]);
        }
        $payment->markAsFailed();
        $entityManager->flush();
        $this->addFlash('success', 'Paiement échoué.');
        return $this->redirectToRoute('app_profile_booking_show', ['booking' => $booking->getId()]);
    }
}
