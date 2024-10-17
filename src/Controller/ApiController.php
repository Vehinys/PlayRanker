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
                $averageScores[$game['id']] = null; // ou une valeur par défaut comme "N/A"
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

        // Paramètre d'URL pour l'ID du jeu
        string $id,

        // Objet Request pour gérer la requête HTTP
        Request $request,

        // Client HTTP pour les appels API
        ApiHttpClient $apiHttpClient,

        // Repository pour accéder aux données des jeux
        GameRepository $gameRepository,

        // Repository pour accéder aux scores
        ScoreRepository $scoreRepository,

        // Repository pour accéder aux commentaires
        CommentRepository $commentRepository,

        // Gestionnaire d'entités pour la persistance des données
        EntityManagerInterface $entityManager,

        // Repository pour accéder aux catégories de notation
        RatingCategoryRepository $ratingCategoryRepository,

    ): Response {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Récupère les détails du jeu depuis l'API
        $gameDetail = $apiHttpClient->gameDetail($id);

        // Récupère les annonces du jeu depuis l'API
        $gameAnnonce = $apiHttpClient->gameAnnonce($id);

        // Cherche le jeu dans la base de données locale
        $game = $gameRepository->findOneBy(['id_game_api' => $id]);

        // Récupère toutes les catégories de notation
        $ratingCategories = $ratingCategoryRepository->findAll();

        // Récupère tous les scores pour ce jeu
        $scores = $scoreRepository->findBy(['game' => $game]);

        // Calcule le score moyen pour ce jeu
        $averageScore = $scoreRepository->getAverageScoreForGame($game);

        // Calcule le score moyen de l'utilisateur pour ce jeu
        $averageScoreUser = $scoreRepository->getAverageScoreForGameAndUser($game, $user);

        // Récupère les commentaires pour ce jeu
        $comments = $commentRepository->findBy(['game' => $game]);

        // Crée le formulaire de notation
        $scoreForm = $this->createForm(ScoreType::class, null, [
            'rating_category_repository' => $ratingCategoryRepository
        ]);

        // Traite la requête pour le formulaire
        $scoreForm->handleRequest($request);

    // Vérifie si le formulaire a été soumis et si les données sont valides
    if ($scoreForm->isSubmitted() && $scoreForm->isValid()) {

        // Vérifie si le jeu n'existe pas déjà dans la base de données
        if (!$game) {
            // Crée une nouvelle instance de l'entité Game
            $game = new Game();
            // Définit l'ID du jeu API
            $game->setIdGameApi($id);
            // Définit le nom du jeu
            $game->setName($gameDetail['name']);
            // Stocke toutes les données du jeu
            $game->setData($gameDetail);
            // Prépare l'entité pour la persistance
            $entityManager->persist($game);
            // Enregistre les changements dans la base de données
            $entityManager->flush();
        }

        // Parcourt toutes les catégories de notation
        foreach ($ratingCategories as $category) {

            // Recherche un score existant pour ce jeu, cet utilisateur et cette catégorie
            $existingScore = $scoreRepository->findOneBy([
                'game' => $game,
                'user' => $this->getUser(),
                'ratingCategory' => $category
            ]);

            // Si un score existe déjà
            if ($existingScore) {

                // Met à jour la note du score existant
                $existingScore->setNote($scoreForm->get('rating' . $category->getId())->getData());

                // Prépare l'entité mise à jour pour la persistance
                $entityManager->persist($existingScore);

            } else {

                // Crée une nouvelle instance de l'entité Score
                $score = new Score();

                // Associe le jeu au score
                $score->setGame($game);

                // Associe l'utilisateur au score
                $score->setUser($this->getUser());

                // Associe la catégorie de notation au score
                $score->setRatingCategory($category);

                // Définit la note pour cette catégorie
                $score->setNote($scoreForm->get('rating' . $category->getId())->getData());

                // Prépare la nouvelle entité pour la persistance
                $entityManager->persist($score);
            }
        }

            // Enregistre les changements dans la base de données
            $entityManager->flush();

            // Ajoute un message de succès
            $this->addFlash('success', 'Vos notes ont été enregistrées avec succès.');

            // Redirige vers la page de détail du jeu
            return $this->redirectToRoute('detail_jeu', ['id' => $id]);
        }

            // Rend le template avec toutes les données nécessaires
            return $this->render('pages/jeux/detail.html.twig', [

                // Passe l'ID du jeu au template
                'gameId' => $id,

                // Transmet tous les scores associés au jeu
                'scores' => $scores,

                // Envoie les commentaires liés au jeu
                'comments' => $comments,

                // Fournit les détails complets du jeu
                'gameDetail' => $gameDetail,

                // Transmet les annonces liées au jeu
                'gameAnnonce' => $gameAnnonce,

                // Passe le score moyen global du jeu
                'averageScore' => $averageScore,

                // Envoie le score moyen de l'utilisateur pour ce jeu
                'averageScoreUser' => $averageScoreUser,

                // Transmet le formulaire de notation au template
                'scoreForm' => $scoreForm->createView(),

                // Fournit toutes les catégories de notation disponibles
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
