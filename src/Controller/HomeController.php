<?php

namespace App\Controller;

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
        $origin = $request->query->get('origin');
        $destination = $request->query->get('destination');
        $dateString = $request->query->get('date');
        $date = null;
        if ($dateString) {
            try {
                $date = new \DateTimeImmutable($dateString);
            } catch (\Exception) {
                $date = null;
            }
        }
        $nextTrips = $tripRepository->findAvailableTrips($origin, $destination, $date);
        return $this->render('home/index.html.twig', [
            'nextTrips' => $nextTrips,
            'origin' => $origin,
            'destination' => $destination,
            'date' => $date
        ]);
    }
}
