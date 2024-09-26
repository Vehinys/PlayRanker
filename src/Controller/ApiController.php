<?php

namespace App\Controller;

use App\HttpClient\ApiHttpClient;
use App\Repository\CategoryRepository;
use App\Repository\PlatformRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Affiche la page d'index pour la section des jeux.
     *
     * Cette action récupère les jeux pour le numéro de page donné en utilisant la méthode `nextPage` du service `ApiHttpClient`.
     * Elle récupère également toutes les catégories depuis le `CategoryRepository` et les transmet au template Twig.
     *
     * @param ApiHttpClient $apiHttpClient Le service client HTTP de l'API.
     * @param Request $request La requête HTTP courante.
     * @param CategoryRepository $repository Le service de repository des catégories.
     *
     * @return Response La réponse rendue.
     */


    #[Route('/jeux', name: 'jeux')]
    public function index( ApiHttpClient $apiHttpClient, Request $request,CategoryRepository $repository): Response 
    {

        $page = $request->query->getInt('page', 1);
        $games = $apiHttpClient->nextPage($page);
        $categories = $repository->findAll();

        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'currentPage' => $page,
            'categories' => $categories

        ]);
    }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Affiche les résultats de recherche des jeux par plateforme.
     *
     * Cette action récupère les jeux pour l'ID de plateforme donné en utilisant la méthode `searchByConsole` du service `ApiHttpClient`.
     * Elle rend ensuite le template `pages/jeux/index.html.twig` avec les jeux récupérés et un paramètre 'token' défini sur 'platform'.
     *
     * @param ApiHttpClient $apiHttpClient Le service client HTTP de l'API.
     * @param string $id L'ID de la plateforme à rechercher.
     *
     * @return Response La réponse rendue.
     */


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

    /**
     * Affiche les résultats de recherche pour les jeux basés sur une saisie utilisateur.
     *
     * Cette action récupère les jeux correspondant à la saisie donnée en utilisant la méthode `gamesSearch` du service `ApiHttpClient`.
     * Elle rend ensuite le template `pages/jeux/index.html.twig` avec les jeux récupérés.
     *
     * @param ApiHttpClient $apiHttpClient Le service client HTTP de l'API.
     * @param Request $request La requête HTTP courante.
     *
     * @return Response La réponse rendue.
     */

    #[Route('/jeux/search', name: 'search', methods: ['POST'])]
    public function search(

        // ApiHttpClient et Request sont les deux paramètres demander par la fonction search

        ApiHttpClient $apiHttpClient, // J'injecte le service ApiHttpClient dans la fonction search pour pouvoir utiliser les fonction qui lui sont associées.
        Request $request // j'injecte la requete dans la fonction search
        
    ): Response {

        // Je defini la variable $input et je stock les donnée saisi par l'utilisateur, en recupérent le champ 'input' de la requete.
        $input = $request->get('input');
        // Je défini la variable Games et je stock la reponse de la requete faite à l'API
        $games = $apiHttpClient->gamesSearch($input);

        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
        ]);
    }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Affiche la page suivante des résultats de recherche pour les jeux.
     *
     * Cette action récupère la page suivante de jeux en utilisant la méthode `nextPage` du service `ApiHttpClient`.
     * Elle rend ensuite le template `pages/jeux/index.html.twig` avec les jeux récupérés et le numéro de page actuel.
     *
     * @param ApiHttpClient $apiHttpClient Le service client HTTP de l'API.
     * @param int $page Le numéro de page à récupérer.
     *
     * @return Response La réponse rendue.
     */

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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Affiche la page suivante des résultats de recherche pour les jeux.
     *
     * Cette action récupère la page suivante de jeux en utilisant la méthode `nextPage` du service `ApiHttpClient`.
     * Elle rend ensuite le template `pages/jeux/index.html.twig` avec les jeux récupérés et le numéro de page actuel.
     *
     * @param ApiHttpClient $apiHttpClient Le service client HTTP de l'API.
     * @param int $page Le numéro de page à récupérer.
     *
     * @return Response La réponse rendue.
     */

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

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */
    
    /**
     * Affiche la page des résultats de recherche pour une sous-catégorie spécifique.
     *
     * Cette action récupère les jeux associés à l'ID de sous-catégorie donné en utilisant la méthode `searchBySousCategory` du service `ApiHttpClient`.
     * Elle récupère également toutes les catégories depuis le `CategoryRepository` pour les passer au template.
     * Le template `pages/jeux/index.html.twig` est ensuite rendu avec les jeux et les catégories récupérés.
     *
     * @param ApiHttpClient $apiHttpClient Le service client HTTP de l'API.
     * @param PlatformRepository $platformRepository Le service de repository des sous-catégories.
     * @param CategoryRepository $categoryRepository Le service de repository des catégories.
     * @param int $id L'ID de la sous-catégorie à rechercher.
     *
     * @return Response La réponse rendue.
     */

    #[Route('/search/platform/{id}', name: 'searchByPlatform')]
    public function searchByPlatform(
        
        ApiHttpClient $apiHttpClient, 
        PlatformRepository $platformRepository, 
        CategoryRepository $categoryRepository, 
        int $id
        
    ): Response {

        $platform = $platformRepository->find($id);
        $games = $platform ? $apiHttpClient->findByPlatform($platform->getName()) : [];
        $categories = $categoryRepository->findAll();
    
        return $this->render('pages/jeux/index.html.twig', [
            'games' => $games,
            'categories' => $categories
        ]);
    }
    
    

}
