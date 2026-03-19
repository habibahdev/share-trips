<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenStreetMapService
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    /**
     * Undocumented function
     *
     * @param string $query
     * @return array<int, array<string, mixed>>
     */
    public function searchCity(string $query): array
    {
        $response = $this->client->request('GET', 'https://nominatim.openstreetmap.org/search', [
            'query' => [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 8,
                'countrycodes' => 'fr'
            ],
            'headers' => [
                'User-Agent' => 'ShareTrips/1.0 constact@sharetrips.fr',
            ]
        ]);
        return $response->toArray();
    }
}
