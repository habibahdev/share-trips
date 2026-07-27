<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\Booking;
use App\Entity\Review;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur responsable des avis utilisateurs.
 */
#[Route('/review', name: 'app_review_')]
final class ReviewController extends AbstractAppController
{
    /**
     * Laisser un avis sur un conducteur.
     *
     * @param Booking $booking Réservation concernée
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Doctrine
     * @param ReviewRepository $reviewRepository Repository des avis
     * @return Response
     */
    #[Route('/add/{id}', name: 'add')]
    public function index(
        Booking $booking,
        Request $request,
        EntityManagerInterface $entityManager,
        ReviewRepository $reviewRepository
    ): Response {
        $user = $this->getAppUser();
        if ($booking->getPassenger()->getId() !== $user->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas noter cette réservation.');
            return $this->redirectToRoute('app_profile_booking');
        }
        if (
            $booking->getStatus() !== BookingStatus::Confirmed
            || $booking->getTrip()->getDepartureAt() > new \DateTimeImmutable()
        ) {
            $this->addFlash('warning', 'Vous ne pouvez pas noter ce conducteur pour l\'instant.');
            return $this->redirectToRoute('app_profile_booking');
        }
        if ($reviewRepository->findByBookingAndReviewer($booking, $user)) {
            $this->addFlash('warning', 'Vous avez déjà noté ce conducteur pour ce trajet.');
            return $this->redirectToRoute('app_profile_booking');
        }
        $driver = $booking->getTrip()->getDriver();
        $review = new Review();
        $review->setReviewer($user);
        $review->setReviewed($driver);
        $review->setBooking($booking);
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();
            $this->addFlash('success', 'Votre avis a bien été enregistré. Merci !');
            return $this->redirectToRoute('app_profile_booking');
        }
        return $this->render('review/add.html.twig', [
            'form' => $form,
            'booking' => $booking,
            'driver' => $driver
        ]);
    }

    /**
     * Affiche le profil public d'un conducteur avec ses avis.
     *
     * @param User $driver Conducteur concerné
     * @param ReviewRepository $reviewRepository Repository des avis
     * @return Response
     */
    #[Route('/driver/{id}', name: 'driver')]
    public function driverProfile(User $driver, ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findByReviewed($driver);
        $averageRating = $reviewRepository->getAverageRating($driver);
        $totalReviews = $reviewRepository->countByReviewed($driver);
        return $this->render('review/driver_profile.html.twig', [
            'driver' => $driver,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'totalReviews' => $totalReviews
        ]);
    }
}
