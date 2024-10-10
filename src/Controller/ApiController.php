<?php

namespace App\Controller;

use App\Entity\Game;
use App\HttpClient\ApiHttpClient;
use App\Repository\TypeRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
use App\Repository\PlatformRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/jeux', name: 'jeux')]
    public function index(
        
        Request $request,
        ApiHttpClient $apiHttpClient, 
        CategoryRepository $repository,
        TypeRepository $typeRepository,

    ): Response {

        $page = $request->query->getInt('page', 1);
        $games = $apiHttpClient->nextPage($page);
        $categories = $repository->findAll();
        $types = $typeRepository->findAll();

        return $this->render('pages/jeux/index.html.twig', [
            'types' => $types,
            'games' => $games,
            'currentPage' => $page,
            'categories' => $categories,
        ]);
    }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/jeux/platforms/{id}', name: 'searchByPlatform')]
    public function searchByConsole(ApiHttpClient $apiHttpClient, string $id): Response
    {   
        $token = 'platform';

        $searchByConsole = $apiHttpClient->searchByConsole($id);
        $games = $searchByConsole;

        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'token' => $token
        ]);
    }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/jeux/search', name: 'search', methods: ['POST'])]
    public function search(

        Request $request, 
        ApiHttpClient $apiHttpClient,
        TypeRepository $typeRepository,
        CategoryRepository $repository,
        
    ): Response {


        $input = $request->get('input');
        $games = $apiHttpClient->gamesSearch($input);
        $types = $typeRepository->findAll();
        $categories = $repository->findAll();

        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'types' => $types,
            'categories' => $categories,

        ]);
    }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */


    #[Route('/jeux/{id}', name: 'detail_jeu')]
    public function detailJeu(
        string $id,
        ApiHttpClient $apiHttpClient,
        CommentRepository $commentRepository,
        GameRepository $gameRepository
    ): Response {
        // Récupérer les détails du jeu depuis l'API
        $gameDetail = $apiHttpClient->gameDetail($id);
        $gameAnnonce = $apiHttpClient->gameAnnonce($id);
    
        // Récupérer l'entité Game correspondante dans la base de données en fonction de id_game_api
        $game = $gameRepository->findOneBy(['id_game_api' => $id]);
    
        // Récupérer les commentaires liés à ce jeu
        $comments = $commentRepository->findBy(['game' => $game]);
    
        return $this->render('pages/jeux/detail.html.twig', [
            'gameDetail' => $gameDetail,
            'gameAnnonce' => $gameAnnonce,
            'gameId' => $id,
            'comments' => $comments
        ]);
    }
    
    
    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/jeux/page/{page}', name: 'jeux_page')]
    public function nextPage(
        
        int $page,
        ApiHttpClient $apiHttpClient, 
        TypeRepository $typeRepository,
        
    ): Response {
        $games = $apiHttpClient->nextPage($page);
        $types = $typeRepository->findAll();

        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'currentPage' => $page,
            'types' => $types,
        ]);
    }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */
    
    #[Route('/search/platform/{id}', name: 'searchByPlatform')]
    public function findByPlatform(

        int $id,
        ApiHttpClient $apiHttpClient, 
        PlatformRepository $platformRepository, 
        CategoryRepository $categoryRepository
        
    ): Response {
    
        $platform = $platformRepository->find($id);
        
        // Vérifie si la plateforme existe, sinon renvoie un tableau vide
        $games = $platform ? $apiHttpClient->findByPlatform($platform->getName()) : [];

    
        $categories = $categoryRepository->findAll();
        $platforms = $platformRepository->findAll();
        
        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'categories' => $categories,
            'platforms' => $platforms,
            'platform' => $platform,
        ]);
    }
    
    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

}
