<?php

namespace App\Controller;

use App\HttpClient\ApiHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/jeux', name: 'jeux')]
    public function index(ApiHttpClient $apiHttpClient, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $games = $apiHttpClient->nextPage($page);

        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'currentPage' => $page
        ]);
    }

    #[Route('/jeux/search', name: 'search', methods: ['POST'])]
    public function search(ApiHttpClient $apiHttpClient, Request $request): Response
    {
        $input = $request->get('input');
        $games = $apiHttpClient->gamesSearch($input);
        
        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/jeux/{id}', name: 'detail_jeu')]
    public function detailJeu(ApiHttpClient $apiHttpClient, string $id): Response
    {
        $gameDetail = $apiHttpClient->gameDetail($id);
        $gameAnnonce = $apiHttpClient->gameAnnonce($id);

        return $this->render('pages/jeux/detail.html.twig', [
            'gameDetail' => $gameDetail,
            'gameAnnonce' => $gameAnnonce
        ]);
    }
    
    #[Route('/jeux/page/{page}', name: 'jeux_page')]
    public function nextPage(ApiHttpClient $apiHttpClient, int $page): Response
    {
        // Utiliser la méthode nextPage de ApiHttpClient pour récupérer les jeux à la page donnée
        $games = $apiHttpClient->nextPage($page);

        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'currentPage' => $page
        ]);
    }


    
}
