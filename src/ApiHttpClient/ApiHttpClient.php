<?php

namespace App\HttpClient;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;



class ApiHttpClient extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function games()
    {
        $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games', [
            'query' => [
                'key' => 'c2caa004df8a4f65b23177fa9ca935f9',
                'page_size' => 100, // Increase the number of results
            ]
        ]);
    
        $data = $response->toArray();
    
        // Sort the results by name
        usort($data['results'], function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
    
        return $data;
    }

    public function gamesSearch($input)
    {
        $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games?key=c2caa004df8a4f65b23177fa9ca935f9&search='.$input.'');
        return $response->toArray();
    }

    public function nextPage($page)
    {
        $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games?key=c2caa004df8a4f65b23177fa9ca935f9&page='.$page.'');
        return $response->toArray();
    }

    public function gameDetail($id)
    {
        $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games/'.$id.'?key=c2caa004df8a4f65b23177fa9ca935f9');
        return $response->toArray();
    }

    public function gameAnnonce($id)
    {
        $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games/'.$id.'/movies?key=c2caa004df8a4f65b23177fa9ca935f9');
        return $response->toArray();
    }

    
}