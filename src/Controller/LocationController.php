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
            $cityName = $r['address']['city']
                    ?? $r['address']['town']
                    ?? $r['address']['village']
                    ?? $r['address']['municipality']
                    ?? $r['display_name'];
            $suburb = $r['address']['suburb'] ?? null;
            $district = $r['address']['city_district'] ?? null;
            $postcode = $r['address']['postcode'] ?? null;
            $state = $r['address']['state'] ?? null;
            $label = $cityName;
            if ($suburb) {
                $label = $cityName . ', ' . $suburb;
            } elseif ($district) {
                $label = $cityName . ', ' . $district;
            } elseif ($postcode) {
                $label = $cityName . ' (' . $postcode . ')';
            } elseif ($state) {
                $label = $cityName . ', ' . $state;
            } else {
                $label = $cityName;
            }
            if (in_array($label, $seen)) {
                continue;
            }
            $seen[] = $label;
            $cities[] = [
                'name' => $r['display_name'],
                'city' => $label,
                'latitude' => $r['lat'],
                'longitude' => $r['lon']
            ];
        }
        return $this->json($cities);
    }
}
