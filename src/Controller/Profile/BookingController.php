<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Enum\BookingStatus;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingController extends AbstractController
{
    #[Route('/profile/booking', name: 'app_profile_booking')]
    public function index(BookingRepository $bookingRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $bookings = $bookingRepository->findByPassenger($user);
        return $this->render('profile/booking/index.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('/profile/booking/{id}', name: 'app_profile_booking_show')]
    public function show(int $id, BookingRepository $bookingRepository): Response
    {
        $booking = $bookingRepository->findOneBy(['id' => $id]);
        if (!$booking) {
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('profile/booking/show.html.twig', [
            'booking' => $booking
        ]);
    }

    #[Route('/profile/booking/cancel/{id}', name: 'app_profile_booking_cancel')]
    public function cancel(
        int $id,
        BookingRepository $bookingRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $booking = $bookingRepository->findOneBy(['id' => $id]);
        if (!$booking) {
            return $this->redirectToRoute('app_profile');
        }
        $booking->setStatus(BookingStatus::Cancelled);
        $entityManager->flush();
        $this->addFlash(
            'success',
            'Réservation annulée.'
        );
        return $this->redirectToRoute('app_profile_booking');
    }
}
