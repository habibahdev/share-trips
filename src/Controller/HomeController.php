<?php

namespace App\Controller;

use App\Dto\TripSearchCriteria;
use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur responsable de la page d'accueil.
 */
final class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil avec les trajets disponibles.
     *
     * @param Request $request Requête HTTP
     * @param TripRepository $tripRepository Repository des trajets
     * @return Response
     */
    #[Route('/', name: 'app_home')]
    public function index(Request $request, TripRepository $tripRepository): Response
    {
        $criteria = TripSearchCriteria::fromRequest($request);

        $nextTrips = $tripRepository->findAvailableTrips(
            $criteria->origin,
            $criteria->destination,
            $criteria->date
        );

        return $this->render('home/index.html.twig', [
            'nextTrips' => $nextTrips,
            'origin' => $criteria->origin,
            'destination' => $criteria->destination,
            'date' => $criteria->date
        ]);
    }
}
