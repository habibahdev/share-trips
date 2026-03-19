<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TripController extends AbstractController
{
    #[Route('/trip', name: 'app_trips')]
    public function index(TripRepository $tripRepository): Response
    {
        $trips = $tripRepository->findBy(['status' => 'open']);
        return $this->render('trip/index.html.twig', [
            'trips' => $trips,
        ]);
    }

    #[Route('/trip/{id}', name: 'app_trip_show')]
    public function show(Trip $trip): Response
    {
        return $this->render('trip/show.html.twig', [
            'trip' => $trip
        ]);
    }
}
