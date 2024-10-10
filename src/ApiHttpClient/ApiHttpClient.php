<?php

namespace App\HttpClient;

use App\Repository\PlatformRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiHttpClient extends AbstractController
{   
    /* ----------------------------------------------------------------------------------------------------------------------------------------- */
    
    /**
     * Propriétés privées de la classe ApiHttpClient.
     *
     * @property HttpClientInterface $httpClient Le client HTTP utilisé pour effectuer les requêtes API.
     * @property string $url L'URL de base pour l'API RAWG.
     * @property string $key La clé API utilisée pour l'API RAWG.
     * @property PlatformRepository $sousCategoryRepository Le repository pour gérer les sous-catégories.
     */

        private $httpClient;
        private $url = 'https://api.rawg.io/api/games?';
        private $key = 'key=c2caa004df8a4f65b23177fa9ca935f9';
        private $platformRepository;

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */
    
    /**
     * Construit une nouvelle instance de la classe ApiHttpClient.
     *
     * @param HttpClientInterface $httpClient Le client HTTP à utiliser pour effectuer les requêtes API.
     * @param PlatformRepository $sousCategoryRepository Le repository pour gérer les sous-catégories.
     */

        public function __construct(HttpClientInterface $httpClient, PlatformRepository $platformRepository)
        {
            $this->httpClient = $httpClient;
            $this->platformRepository = $platformRepository;
        }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Récupère une liste de jeux depuis l'API RAWG.
     *
     * @return array Les données de réponse de l'API RAWG sous forme de tableau.
     */

        public function games()
        {
            $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games?key=c2caa004df8a4f65b23177fa9ca935f9');
            return $response->toArray();
        }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Récupère une liste de jeux qui correspondent à la recherche de l'utilisateur.
     *
     * @param string $input La recherche de l'utilisateur.
     * @return array Les données de réponse de l'API RAWG sous forme de tableau.
     *
     */

        public function gamesSearch($input)
        {
            $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games?key=c2caa004df8a4f65b23177fa9ca935f9&search='.$input.'');
            return $response->toArray();
        }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Récupère la page suivante de données de jeux depuis l'API RAWG.
     *
     * @param int $page Le numéro de page à récupérer.
     * @return array Les données de réponse de l'API RAWG sous forme de tableau.
     */

        public function nextPage($page)
        {
            $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games?key=c2caa004df8a4f65b23177fa9ca935f9&page='.$page.'');
            return $response->toArray();
        }
    
    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Récupère la page suivante de données de jeux depuis l'API RAWG, filtrée par l'ID de plateforme fourni.
     *
     * @param int $page Le numéro de page à récupérer.
     * @param int $id L'ID de la plateforme pour filtrer les jeux.
     * @return array Les données de réponse de l'API RAWG sous forme de tableau.
     */

        public function nextPagePlatform($page, $id)
        {
            $url = $this->url;
            $key = $this->key;
            $response = $this->httpClient->request('GET', $url.$key.'&page='.$page.'&platforms='.$id);

            return $response->toArray();
        }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Récupère les informations détaillées d'un jeu spécifique depuis l'API RAWG.
     *
     * @param int $id L'ID du jeu à récupérer.
     * @return array Les données de réponse de l'API RAWG sous forme de tableau.
     */

        public function gameDetail($id)
        {
            $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games/'.$id.'?key=c2caa004df8a4f65b23177fa9ca935f9');
            return $response->toArray();
        }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Récupère les données vidéo pour un jeu spécifique depuis l'API RAWG.
     *
     * @param int $id L'ID du jeu pour lequel récupérer les données vidéo.
     * @return array Les données de réponse de l'API RAWG sous forme de tableau.
     */

        public function gameAnnonce($id)
        {
            $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games/'.$id.'/movies?key=c2caa004df8a4f65b23177fa9ca935f9');
            return $response->toArray();
        }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    /**
     * Récupère la page suivante de données de jeux depuis l'API RAWG, filtrée par l'ID de plateforme fourni.
     *
     * @param int $id L'ID de la plateforme pour filtrer les jeux.
     * @return array Les données de réponse de l'API RAWG sous forme de tableau.
     */

    
        public function searchByConsole(string $id): array {
            // Ajoutez des logs pour vérifier les paramètres
            error_log("Recherche de jeux pour la console ID: " . $id);
        
            // Effectuez la requête à l'API avec l'URL complète
            $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games?platforms=' . $id . '&key=c2caa004df8a4f65b23177fa9ca935f9');
        
            // Vérifiez les résultats de l'API
            $data = $response->toArray();
            error_log("Résultats de l'API: " . json_encode($data));
        
            return $data;
        }
        

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

        public function gamesByCategory($categoryId)
        {
            $response = $this->httpClient->request('GET', $this->url . $this->key . '&genres=' . $categoryId);
            return $response->toArray();
        }
    
    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

}