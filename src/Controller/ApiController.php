<?php

namespace App\Controller;

use App\HttpClient\ApiHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index( ApiHttpClient $apiHttpClient): Response
    {
        $games = $apiHttpClient->games();

        return $this->render('api/index.html.twig', [
            'games' => $games
        ]);
    }

    #[Route('/api/search/{input}', name: 'search')]
    public function search( ApiHttpClient $apiHttpClient, $input): Response
    {
        $games = $apiHttpClient->gamesSearch($input);


        return $this->render('api/index.html.twig', [
            'games' => $games
        ]);
    }



}