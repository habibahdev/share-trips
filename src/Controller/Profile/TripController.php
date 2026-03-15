<?php

namespace App\Controller\Profile;

use App\Entity\Trip;
use App\Entity\User;
use App\Enum\TripStatus;
use App\Form\TripType;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TripController extends AbstractController
{
    #[Route('/profile/trip', name: 'app_profile_trip')]
    public function index(TripRepository $tripRepository): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $trips = $tripRepository->findByDriver($user);
        return $this->render('profile/trip/index.html.twig', [
            'trips' => $trips,
        ]);
    }

    #[Route('/profile/trip/add/{id}', name: 'app_profile_trip_form', defaults: ['id' => null])]
    public function form(
        ?int $id,
        TripRepository $tripRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($id) {
            $trip = $tripRepository->findOneBy(['id' => $id]);
            if (!$trip || $trip->getDriver() !== $user) {
                return $this->redirectToRoute('app_profile');
            }
        } else {
            $trip = new Trip();
            $trip->setDriver($user);
        }
        $form = $this->createForm(TripType::class, $trip, ['vehicles' => $user->getVehicles()]);
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
            'title' => 'Gestion de mon trajet',
            'trip' => $trip
        ]);
    }

    #[Route('/profile/trip/cancel/{id}', name: 'app_profile_trip_cancel')]
    public function cancel(
        TripRepository $tripRepository,
        int $id,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        $trip = $tripRepository->findOneBy(['id' => $id]);
        if (!$trip || $trip->getDriver() !== $user) {
            return $this->redirectToRoute('app_profile');
        }
        $trip->setStatus(TripStatus::Cancelled);
        $entityManager->flush();
        $this->addFlash(
            'success',
            'Trajet annulé.'
        );
        return $this->redirectToRoute('app_profile_trip');
    }

    #[Route('/profile/trip/{id}', name: 'app_profile_trip_show')]
    public function show(
        TripRepository $tripRepository,
        int $id
    ): Response {
        $trip = $tripRepository->findOneBy(['id' => $id]);
        if (!$trip) {
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('profile/trip/show.html.twig', [
            'trip' => $trip
        ]);
    }
}
