<?php

namespace App\Controller;

use App\HttpClient\ApiHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/jeux', name: 'jeux')]
    public function index( ApiHttpClient $apiHttpClient): Response
    {
        $games = $apiHttpClient->games();
        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/jeux/search', name: 'search', methods:'POST')]
    public function search( ApiHttpClient $apiHttpClient, Request $request): Response
    {
 
        $input = $request->get('input');
        $games = $apiHttpClient->gamesSearch($input);

        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games
        ]);
    }
}