<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Report;
use App\Entity\User;
use App\Form\ReportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur responsable du signalement entre utilisateurs.
 */
final class ReportController extends AbstractController
{
    /**
     * Création d'un signalement par rapport à un trajet
     *
     * @param Booking $booking Réservation concernée
     * @param Request $request Requête HTTP
     * @param EntityManagerInterface $entityManager Doctrine
     * @return Response
     */
    #[Route('/report/{id}', name: 'app_report_add')]
    public function add(
        Booking $booking,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($booking->getTrip()->getDepartureAt() > new \DateTimeImmutable()) {
            $this->addFlash('danger', 'Vous ne pouvez signaler qu\'après le trajet.');
            return $this->redirectToRoute('app_profile_booking');
        }
        if ($booking->getPassenger()->getId() === $user->getId()) {
            $reported = $booking->getTrip()->getDriver();
        } elseif ($booking->getTrip()->getDriver()->getId() === $user->getId()) {
            $reported = $booking->getPassenger();
        } else {
            return $this->redirectToRoute('app_home');
        }
        $existing = $entityManager->getRepository(Report::class)->findOneBy([
            'reporter' => $user,
            'booking' => $booking
        ]);
        if ($existing) {
            $this->addFlash('warning', 'Vous avez déjà signalé cette personne pour ce trajet.');
            return $this->redirectToRoute('app_profile_booking');
        }
        $report = new Report();
        $report->setReported($reported);
        $report->setReporter($user);
        $report->setBooking($booking);
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($report);
            $entityManager->flush();
            $this->addFlash('success', 'Signalement envoyé. Notre équipe va examiner votre demande.');
            return $this->redirectToRoute('app_profile_booking');
        }
        return $this->render('report/index.html.twig', [
            'reported' => $reported,
            'booking' => $booking,
            'form' => $form,
        ]);
    }
}
