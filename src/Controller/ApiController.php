<?php

namespace App\Controller;

use App\HttpClient\ApiHttpClient;
use App\Repository\TypeRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use App\Repository\GameRepository;
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
        CategoryRepository $categoryRepository,
        TypeRepository $typeRepository,

    ): Response {

        $page = $request->query->getInt('page', 1);
        $games = $apiHttpClient->nextPage($page);
        $categories = $categoryRepository->findAll();
        $types = $typeRepository->findAll();
    
        return $this->render('pages/jeux/index.html.twig', [
            'types' => $types,
            'games' => $games,
            'currentPage' => $page,
            'categories' => $categories,
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
    
    #[Route('/jeux/platforms/{id}', name: 'searchByPlatform')]
    public function searchByConsole(
        string $id,
        ApiHttpClient $apiHttpClient,
        CategoryRepository $categoryRepository
    ): Response {  
        $token = 'platform';
        $searchByConsole = $apiHttpClient->searchByConsole($id);
        $games = $searchByConsole;
        $categories = $categoryRepository->findAll();
    
        if (empty($games['results'])) {
            $this->addFlash('notice', 'No games found for this platform.');
        }
    
        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'token' => $token,
            'categories' => $categories
        ]);
    }
}
