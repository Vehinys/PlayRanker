<?php

namespace App\HttpClient;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiHttpClient extends AbstractController
{
    private $httpClient;
    private $url = 'https://api.rawg.io/api/games?';
    private $key = 'key=c2caa004df8a4f65b23177fa9ca935f9';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function games()
    {
        $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games?key=c2caa004df8a4f65b23177fa9ca935f9');
        return $response->toArray();
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
    
    public function nextPagePlatform($page, $id)
    {

        $url = $this->url;
        $key = $this->key;

        $response = $this->httpClient->request('GET', $url.$key.'&page='.$page.'&platforms='.$id);

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

    public function searchByConsole($id)
    {
        $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games?key=c2caa004df8a4f65b23177fa9ca935f9&platforms='.$id.'');
        return $response->toArray();
    }

}