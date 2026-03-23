<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TripController extends AbstractController
{
    #[Route('/trip', name: 'app_trips')]
    public function index(TripRepository $tripRepository, Request $request): Response
    {
        $origin = $request->query->get('origin');
        $destination = $request->query->get('destination');
        $dateString = $request->query->get('date');
        $date = $dateString ? new \DateTimeImmutable($dateString) : null;

        $trips = $tripRepository->findAvailableTrips($origin, $destination, $date);
        return $this->render('trip/index.html.twig', [
            'trips' => $trips,
            'origin' => $origin,
            'destination' => $destination,
            'date' => $date,
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
