<?php

namespace App\Controller;

use App\Service\OpenStreetMapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API responsable de la localisation
 */
class LocationController extends AbstractController
{
    /**
     * Recherche des villes/adresses via une requête texte.
     *
     * @param Request $request Requête HTTP contenant le paramètre 'q'
     * @param OpenStreetMapService $streetMap Service de recherche géographique
     * @return JsonResponse
     */
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
            $props = $r['properties'];
            $coords = $r['geometry']['coordinates'];

            $name = $props['name'] ?? null;
            $city = $props['city'] ?? $props['town'] ?? $props['village'] ?? null;
            $street = $props['street'] ?? null;
            $postcode = $props['postcode'] ?? null;
            $state = $props['state'] ?? null;

            if ($name && $city && $name !== $city) {
                $label = $name . ', ' . $city;
            } elseif ($street && $city) {
                $label = $street . ', ' . $city;
            } elseif ($city && $postcode) {
                $label = $city . ' (' . $postcode . ')';
            } elseif ($name && $state) {
                $label = $name . ', ' . $state;
            } else {
                $label = $name ?? $city ?? 'Lieu inconnu';
            }
            if (in_array($label, $seen, true)) {
                continue;
            }
            $seen[] = $label;
            $cities[] = [
                'city' => $label,
                'latitude' => (string) $coords[1],
                'longitude' => (string) $coords[0],
            ];
        }
        return $this->json($cities);
    }
}
