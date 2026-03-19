<?php

namespace App\Controller;

use App\Service\OpenStreetMapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LocationController extends AbstractController
{
    #[Route('/api/cities/search', name: 'api_cities_search', methods: ['GET'])]
    public function search(Request $request, OpenStreetMapService $streetMap): JsonResponse
    {
        $query = $request->query->get('q', '');
        if (strlen($query) < 2) {
            return $this->json([]);
        }
        $results = $streetMap->searchCity($query);
        $cities = [];
        $seen = [];
        foreach ($results as $r) {
            $label = $r['display_name'];
            if (in_array($label, $seen, true)) {
                continue;
            }
            $seen[] = $label;
            $cities[] = [
                'label' => $label,
                'city' => $r['address']['city'] ?? $r['address']['town'] ?? $r['address']['village'] ?? null,
                'road' => $r['address']['road'] ?? null,
                'suburb' => $r['address']['suburb'] ?? null,
                'postcode' => $r['address']['postcode'] ?? null,
                'state' => $r['address']['state'] ?? null,
                'country' => $r['address']['country'] ?? null,
                'latitude' => $r['lat'],
                'longitude' => $r['lon'],
            ];
        }
        return $this->json($cities);
    }
}
