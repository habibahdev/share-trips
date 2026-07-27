<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Booking;
use App\Entity\Payment;
use App\Enum\BookingStatus;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\TripRepository;
use App\Security\BookingVoter;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur responsable de la gestion des réservations.
 */
#[Route('/booking', name: 'app_booking_')]
final class BookingController extends AbstractAppController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Réserver un trajet.
     *
     * @param integer $tripId Identifiant du trajet à réserver
     * @param TripRepository $tripRepository Repository des trajets
     * @param Request $request Requête HTTP
     * @param StripeService $stripe
     * @return Response
     */
    #[Route('/add/{tripId}', name: 'add')]
    public function add(
        int $tripId,
        TripRepository $tripRepository,
        Request $request,
        StripeService $stripe,
        BookingRepository $bookingRepository
    ): Response {
        $user = $this->getAppUser();

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
        if ($bookingRepository->hasActiveBooking($trip, $user)) {
            $this->addFlash('warning', 'Vous avez déjà une réservation active pour ce trajet.');
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
            $this->entityManager->persist($booking);
            $this->entityManager->persist($payment);
            $this->entityManager->flush();
            $session = $stripe->createCheckoutSession($booking, $payment);
            $payment->setStripeSessionId($session->id);
            $this->entityManager->flush();
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
    #[Route('/{booking}/stripe/success', name: 'stripe_success')]
    public function stripeSuccess(Booking $booking): Response
    {
        $this->getAppUser();
        $this->denyAccessUnlessGranted(BookingVoter::VIEW, $booking);
        return $this->render('booking/success.html.twig', [
            'booking' => $booking,
            'trip' => $booking->getTrip()
        ]);
    }

    /**
     * Undocumented function
     *
     * @param Booking $booking
     * @return Response
     */
    #[Route('/{booking}/stripe/cancel', name: 'stripe_cancel')]
    public function stripeCancel(Booking $booking): Response
    {
        $this->getAppUser();
        $this->denyAccessUnlessGranted(BookingVoter::VIEW, $booking);
        $payment = $booking->getPayment();
        if ($payment && !$payment->getStatus()->isFinal()) {
            $payment->markAsFailed();
            $booking->setStatus(BookingStatus::Cancelled);
            $this->entityManager->flush();
        }
        $this->addFlash('warning', 'Paiement annulé. Votre réservation n\'a pas été confirmée.');
        return $this->redirectToRoute('app_trip_show', [
            'id' => $booking->getTrip()->getId()
        ]);
    }

    /**
     * Undocumented function
     *
     * @param Booking $booking
     * @return Response
     */
    #[Route('/{booking}/success', name: 'add_success')]
    public function success(Booking $booking): Response
    {
        $this->getAppUser();
        $this->denyAccessUnlessGranted(BookingVoter::VIEW, $booking);
        return $this->render('booking/success.html.twig', [
            'booking' => $booking,
            'trip' => $booking->getTrip(),
        ]);
    }
}
