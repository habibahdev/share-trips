<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenStreetMapService
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchCity(string $query): array
    {
        $response = $this->client->request('GET', 'https://photon.komoot.io/api/', [
            'query' => [
                'q' => $query,
                'limit' => 10,
                'lang' => 'fr',
                'bbox' => '-5.142,41.333,9.561,51.089'
            ],
            'headers' => [
                'User-Agent' => 'ShareTrips/1.0 constact@sharetrips.fr',
            ]
        ]);
        $data = $response->toArray();
        return $data['features'] ?? [];
    }
}
