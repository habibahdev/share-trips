<?php

namespace App\Controller;

use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TripRepository $tripRepository): Response
    {
        $nextTrips = $tripRepository->findNextAvailableTrips(6);
        return $this->render('home/index.html.twig', [
            'nextTrips' => $nextTrips
        ]);
    }
}
