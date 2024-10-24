<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DiscordApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DiscordController extends AbstractController
{

    private string $clientId;
    private string $redirectUri;
    private string $clientSecret;
    
    public function __construct(
        private readonly DiscordApiService $discordApiService,
        string $clientId,
        string $redirectUri,
        string $clientSecret
    ) {
        $this->clientId = $clientId;
        $this->redirectUri = $redirectUri;
        $this->clientSecret = $clientSecret;
    }

/* *************************************************************************************************************************** */

    #[Route('/discord/connect', name: 'oauth_discord', methods: ['POST'])]
    public function connect(
        
        Request $request
        
    ): Response {

        // Récupère le token CSRF envoyé dans la requête.
        $token = $request->request->get('token');

        // Vérifie si le token CSRF est valide.
        if ($this->isCsrfTokenValid('discord-auth', $token)) {
            // Si le token est valide, stocke une clé dans la session pour indiquer que l'authentification Discord a été initiée.
            $request->getSession()->set('discord-auth', true);
            
            // Définit les scopes requis pour l'authentification (ici, 'identify' pour les informations de base et 'email' pour récupérer l'email).
            $scope = ['identify', 'email'];
            
            // Redirige l'utilisateur vers l'URL d'autorisation de Discord en utilisant les scopes définis.
            return $this->redirect($this->discordApiService->getAuthorizationUrl($scope));
        }

        // Si le token CSRF n'est pas valide, redirige l'utilisateur vers la page d'accueil.
        return $this->redirectToRoute('home');
    }


/* *************************************************************************************************************************** */

    #[Route('/discord/auth', name: 'oauth_discord_auth')]
    public function auth(): Response
    {
        return $this->redirectToRoute('home');
    }

/* *************************************************************************************************************************** */

    #[Route('/discord/check', name: 'oauth_discord_check')]
    public function check(
        
        EntityManagerInterface $em, 
        Request $request, 
        UserRepository $userRepo
        
        ): Response {

        // Récupération du code d'autorisation transmis par Discord après l'authentification
        $authorizationCode = $request->query->get('code');
        
        // Si aucun code n'est présent, afficher un message d'erreur
        if (!$authorizationCode) {
            return $this->render('pages/security/check.html.twig', [
                'error' => 'No authorization code received from Discord.'
            ]);
        }

        // Création d'un client HTTP pour faire des requêtes à l'API Discord
        $client = HttpClient::create();
        
        // Envoi d'une requête POST à Discord pour échanger le code d'autorisation contre un jeton d'accès
        $response = $client->request('POST', 'https://discord.com/api/oauth2/token', [
            'body' => [
                'client_id' =>  $this->clientId,  // Client ID défini dans la configuration
                'client_secret' => $this->clientSecret,  // Secret défini dans la configuration
                'grant_type' => 'authorization_code',  // Type d'autorisation
                'code' => $authorizationCode,  // Le code d'autorisation reçu de Discord
                'redirect_uri' => $this->redirectUri,  // URI de redirection enregistrée
            ],
        ]);

        // Conversion de la réponse en tableau associatif
        $data = $response->toArray(false);

        // Si un jeton d'accès est présent dans la réponse
        if (isset($data['access_token'])) {
            $accessToken = $data['access_token'];  // Récupérer le jeton d'accès

            // Utiliser ce jeton pour récupérer les informations utilisateur depuis Discord
            $discordUser = $this->discordApiService->fetchUser($accessToken);
            
            // Si la récupération des informations échoue, afficher un message d'erreur
            if (!$discordUser) {
                return $this->render('pages/security/check.html.twig', [
                    'error' => 'Failed to retrieve user information from Discord.'
                ]);
            }

            // Rechercher l'utilisateur dans la base de données par son discordId
            $user = $userRepo->findOneBy(['discordId' => $discordUser->id]);

            // Si l'utilisateur existe déjà
            if ($user) {
                $user->setAccessToken($accessToken);  // Mettre à jour le jeton d'accès
                $em->flush();  // Sauvegarder les changements dans la base de données

                // Rediriger vers la route 'oauth_discord_auth' avec le jeton d'accès
                return $this->redirectToRoute('oauth_discord_auth', [
                    'accessToken' => $accessToken,
                ]);
            } else {
                // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
                $user = new User();
                $user->setAccessToken($accessToken);  // Assigner le jeton d'accès
                $user->setUsername($discordUser->username);  // Assigner le nom d'utilisateur Discord
                $user->setEmail($discordUser->email);  // Assigner l'email Discord
                $user->setAvatar($discordUser->avatar);  // Assigner l'avatar Discord
                $user->setDiscordId($discordUser->id);  // Assigner l'ID Discord

                $em->persist($user);  // Ajouter le nouvel utilisateur à l'entité manager
                $em->flush();  // Sauvegarder l'utilisateur dans la base de données

                // Rediriger vers la route 'oauth_discord_auth' avec le jeton d'accès
                return $this->redirectToRoute('oauth_discord_auth', [
                    'accessToken' => $accessToken,
                ]);
            }
        // Si la réponse contient une erreur
        } elseif (isset($data['error'])) {
            return $this->render('pages/security/check.html.twig', [
                'error' => 'Error: ' . $data['error']  // Afficher l'erreur retournée par Discord
            ]);
        } else {
            // Si aucune donnée utile n'est reçue, afficher un message d'échec de récupération du jeton
            return $this->render('pages/security/check.html.twig', [
                'error' => 'Failed to retrieve access token from Discord.'
            ]);
        }
    }



}