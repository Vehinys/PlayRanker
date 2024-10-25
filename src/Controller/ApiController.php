<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Score;
use App\Form\ScoreType;
use App\HttpClient\ApiHttpClient;
use App\Repository\GameRepository;
use App\Repository\TypeRepository;
use App\Repository\ScoreRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RatingCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ApiController extends AbstractController
{

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/jeux/{page<\d+>?1}', name: 'jeux', methods:['POST', 'GET'])]
    public function index(

        int $page, 
        ApiHttpClient $apiHttpClient,  
        CategoryRepository $categoryRepository, 
        TypeRepository $typeRepository, 
        ScoreRepository $scoreRepository,
        GameRepository $gameRepository
        
    ): Response {

        if (!$page) {
            $page = 1;
        }

        // Récupération des jeux pour la page actuelle
        $games = $apiHttpClient->nextPage($page);
        
        // Calcul des scores moyens pour chaque jeu
        $averageScores = [];

        foreach ($games['results'] as $game) {

            $gameEntity = $gameRepository->findOneBy(['id_game_api' => $game['id']]);

            $averageScores[$game['id']] = $gameEntity ? 

            $scoreRepository->getAverageScoreForGame($gameEntity) : null;
        }
    
        // Récupérer toutes les catégories et types disponibles
        $categories = $categoryRepository->findAll();
        $types = $typeRepository->findAll();
    
        // Rendu du template avec les jeux, catégories, types et scores
        return $this->render('pages/jeux/index.html.twig', [
            'types'       => $types,
            'games'       => $games,
            'currentPage' => $page,
            'categories'  => $categories,
            'averageScores' => $averageScores,
        ]);
    }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/jeux/search/{page<\d+>?1}', name: 'search', methods: ['GET', 'POST'])]
    public function search(

        int $page, 
        Request $request, 
        ApiHttpClient $apiHttpClient,
        TypeRepository $typeRepository,
        CategoryRepository $repository,
        GameRepository $gameRepository,
        ScoreRepository $scoreRepository,
        SessionInterface $session

    ): Response {

        // Vérifier si l'input est présent dans la requête
        $input = $request->get('input');
        
        // Si l'input est présent, on le stocke dans la session
        if ($input) {
            $session->set('search_input', $input);
        } else {
            // Si pas d'input dans la requête, on récupère l'input de la session
            $input = $session->get('search_input');
        }

        // Récupération des résultats de recherche
        $games = $apiHttpClient->nextPageSearch($page, $input);

        if (!$page) {
            $page = 1;
        }

        // Récupération des types et catégories
        $types = $typeRepository->findAll();

        $categories = $repository->findAll();
        
        $averageScores = [];
        
        foreach ($games['results'] as $game) {
            $gameEntity = $gameRepository->findOneBy(['id_game_api' => $game['id']]);
            if ($gameEntity) {
                $averageScores[$game['id']] = $scoreRepository->getAverageScoreForGame($gameEntity);
            } else {
                $averageScores[$game['id']] = null;
            }
        }

        return $this->render('pages/jeux/index.html.twig', [
            'currentSearchPage' => $page,
            'games' => $games,
            'types' => $types,
            'categories' => $categories,
            'averageScores' => $averageScores,
        ]);
    }


    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    // Définit la route pour la page de détail d'un jeu
        #[Route('/jeux/detail/{id}', name: 'detail_jeu')]
        public function detailJeu(

            string $id,
            ApiHttpClient $apiHttpClient,
            GameRepository $gameRepository,
            ScoreRepository $scoreRepository,
            CommentRepository $commentRepository,
            EntityManagerInterface $entityManager,
            RatingCategoryRepository $ratingCategoryRepository,
            Request $request

        ): Response {

            $user = $this->getUser();
            $gameDetail = $apiHttpClient->gameDetail($id);
            $gameAnnonce = $apiHttpClient->gameAnnonce($id);
            $game = $gameRepository->findOneBy(['id_game_api' => $id]);
            $ratingCategories = $ratingCategoryRepository->findAll();
            $scores = $scoreRepository->findBy(['game' => $game]);

            // Calculate average score
            $averageScore = $game ? $scoreRepository->getAverageScoreForGame($game) : null;

            if ($user) {
                $averageScoreUser = $game ? $scoreRepository->getAverageScoreForGameAndUser($game, $user) : null;

                } else {
                
                $averageScoreUser = null;
    
            }

            $comments = $commentRepository->findBy(['game' => $game]);

            $scoreForm = $this->createForm(ScoreType::class, null, [
                'rating_category_repository' => $ratingCategoryRepository
            ]);

            $scoreForm->handleRequest($request);

            if ($scoreForm->isSubmitted() && $scoreForm->isValid()) {
                if (!$game) {
                    $game = new Game();
                    $game->setIdGameApi($id);
                    $game->setName($gameDetail['name']);
                    $game->setData($gameDetail);
                    $entityManager->persist($game);
                    $entityManager->flush();
                }

                foreach ($ratingCategories as $category) {
                    $existingScore = $scoreRepository->findOneBy([
                        'game' => $game,
                        'user' => $this->getUser(),
                        'ratingCategory' => $category
                    ]);

                    if ($existingScore) {
                        $existingScore->setNote($scoreForm->get('rating' . $category->getId())->getData());
                        $entityManager->persist($existingScore);
                    } else {
                        $score = new Score();
                        $score->setGame($game);
                        $score->setUser($this->getUser());
                        $score->setRatingCategory($category);
                        $score->setNote($scoreForm->get('rating' . $category->getId())->getData());
                        $entityManager->persist($score);
                    }
                }

                $entityManager->flush();
                $this->addFlash('success', 'Vos notes ont été enregistrées avec succès.');
                return $this->redirectToRoute('detail_jeu', ['id' => $id]);
            }

            return $this->render('pages/jeux/detail.html.twig', [
                'gameId' => $id,
                'scores' => $scores,
                'comments' => $comments,
                'gameDetail' => $gameDetail,
                'gameAnnonce' => $gameAnnonce,
                'averageScore' => $averageScore,
                'averageScoreUser' => $averageScoreUser,
                'scoreForm' => $scoreForm->createView(),
                'ratingCategories' => $ratingCategories,
            ]);
        }


    /* ----------------------------------------------------------------------------------------------------------------------------------------- */
    
    #[Route('/jeux/platforms/{id}', name: 'searchByPlatform')]
    public function searchByConsole(

        string $id,
        ApiHttpClient $apiHttpClient,
        CategoryRepository $categoryRepository,
        GameRepository $gameRepository,
        ScoreRepository $scoreRepository

    ): Response {  

        $token = 'platform';
        $searchByConsole = $apiHttpClient->searchByConsole($id);
        $games = $searchByConsole;
        $categories = $categoryRepository->findAll();
    
        if (empty($games['results'])) {
            $this->addFlash('notice', 'No games found for this platform.');
        }

        $averageScores = [];
        
        foreach ($games['results'] as $game) {
            $gameEntity = $gameRepository->findOneBy(['id_game_api' => $game['id']]);
            if ($gameEntity) {
                $averageScores[$game['id']] = $scoreRepository->getAverageScoreForGame($gameEntity);
            } else {
                $averageScores[$game['id']] = null;
            }
        }
    
        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'token' => $token,
            'categories' => $categories,
            'averageScores' => $averageScores,
        ]);
    }

    #[Route('/games/scores', name: 'scores')]
    public function findAllScoresGroupedByUserGameAndCategory(

        ScoreRepository $scoreRepository
    
    ): Response {
        
        $scores = $scoreRepository->findAllScoresGroupedByUserGameAndCategory();

        return $this->render('scores/index.html.twig', [
            'scores' => $scores,
        ]);
    }

}
