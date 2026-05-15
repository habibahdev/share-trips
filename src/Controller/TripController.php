<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Repository\TripRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur responsable de l'affichage et de la recherche de trajets.
 */
final class TripController extends AbstractController
{
    /**
     * Affiche la liste des trajets disponibles avec filtres.
     *
     * @param TripRepository $tripRepository Repository des trajets
     * @param Request $request Requête HTTP
     * @return Response
     */
    #[Route('/trip', name: 'app_trips')]
    public function index(
        TripRepository $tripRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
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
        $query = $tripRepository->findAvailableTripsQuery($origin, $destination, $date);
        $trips = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            9
        );
        return $this->render('trip/index.html.twig', [
            'trips' => $trips,
            'origin' => $origin,
            'destination' => $destination,
            'date' => $date,
        ]);
    }

    /**
     * Affiche le détail d'un trajet.
     *
     * @param Trip $trip Trajet à effectuer
     * @return Response
     */
    #[Route('/trip/{id}', name: 'app_trip_show')]
    public function show(Trip $trip): Response
    {
        return $this->render('trip/show.html.twig', [
            'trip' => $trip
        ]);
    }
}
