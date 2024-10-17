<?php

namespace App\Controller;

use App\Entity\Score;
use App\Form\ScoreType;
use App\Entity\Game;
use App\Entity\Post;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ApiController extends AbstractController
{

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    #[Route('/jeux/{page}', name: 'jeux', methods:['POST', 'GET'])]
    public function index(
    
        Int $page, // Paramètre pour la pagination
        ApiHttpClient $apiHttpClient,  // Service pour interagir avec l'API des jeux
        CategoryRepository $categoryRepository, // Repository pour accéder aux catégories
        TypeRepository $typeRepository, // Repository pour accéder aux types
        ScoreRepository $scoreRepository,
        GameRepository $gameRepository,

    ): Response {
    
        // Si la variable $page est définie, récupérer la page suivante des jeux
        if (!$page) {

            // Récupération du numéro de page à partir de la requête (par défaut 1 si non défini)
            // $page = $request->query->getInt('page', 1);
            $page = 1;
            
        }

        $games = $apiHttpClient->nextPage($page);
        $averageScores = [];
        
        foreach ($games['results'] as $game) {
            $gameEntity = $gameRepository->findOneBy(['id_game_api' => $game['id']]);
            if ($gameEntity) {
                $averageScores[$game['id']] = $scoreRepository->getAverageScoreForGame($gameEntity);
            } else {
                $averageScores[$game['id']] = null;
            }
        }
    
        // Récupérer toutes les catégories disponibles dans la base de données
        $categories = $categoryRepository->findAll();
    
        // Récupérer tous les types disponibles dans la base de données
        $types = $typeRepository->findAll();
        
        // Rendu du template Twig 'index.html.twig' avec les données des jeux, types, catégories, et la page actuelle
        return $this->render('pages/jeux/index.html.twig', [

            'types'       => $types,     // Les types récupérés pour le filtre ou l'affichage
            'games'       => $games,     // Les jeux récupérés pour la page actuelle
            'currentPage' => $page,      // Numéro de la page courante
            'categories'  => $categories, // Les catégories récupérées pour le filtre ou l'affichage
            'averageScores' => $averageScores,
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

            $averageScoreUser = $game ? $scoreRepository->getAverageScoreForGameAndUser($game, $user) : null;

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
