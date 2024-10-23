<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DiscordApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DiscordController extends AbstractController
{
    public function __construct(
        private readonly DiscordApiService $discordApiService,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    )
    {
    }



    #[Route('/discord/connect', name: 'oauth_discord', methods: ['POST'])]
    public function connect(Request $request): Response
    {
        $token = $request->request->get('token');

        if ($this->isCsrfTokenValid('discord-auth', $token)) {
            $request->getSession()->set('discord-auth', true);
            $scope = ['identify', 'email'];
            return $this->redirect($this->discordApiService->getAuthorizationUrl($scope));
        }

        return $this->redirectToRoute('home');
    }





    #[Route('/discord/auth', name: 'oauth_discord_auth')]
    public function auth(): Response
    {
        return $this->redirectToRoute('home');
    }



    

    #[Route('/discord/check', name: 'oauth_discord_check', methods: ['GET', 'POST'])]
    public function check(Request $request): Response
    {
        $accessToken = null;

        // Vérifier si la requête est POST ou GET
        if ($request->isMethod('POST')) {
            // Récupérer le body JSON de la requête POST
            $data = json_decode($request->getContent(), true);
            $accessToken = $data['access_token'] ?? null;
        } else {
            // Pour les requêtes GET, récupérer le token dans l'URL
            $accessToken = $request->query->get('access_token');
        }

        if (!$accessToken) {
            return new Response('Access token missing', Response::HTTP_BAD_REQUEST);
        }

        // Récupération des données utilisateur via Discord
        try {
            $discordUser = $this->discordApiService->fetchUser($accessToken);
        } catch (\Exception $e) {
            return new Response('Erreur lors de la récupération des données utilisateur: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        // Vérification si l'utilisateur existe déjà
        $user = $this->userRepository->findOneBy(['discordId' => $discordUser->id]);

        if ($user) {
            // Authentifier l'utilisateur
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $request->getSession()->set('_security_main', serialize($token));

            return $this->redirectToRoute('dashboard'); // Redirection vers le tableau de bord
        }

        // Création d'un nouvel utilisateur
        $user = new User();
        $user->setAccessToken($accessToken);
        $user->setUsername($discordUser->username);
        $user->setEmail($discordUser->email);
        $user->setAvatar($discordUser->avatar);
        $user->setDiscordId($discordUser->id);

        // Enregistrement en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Authentifier le nouvel utilisateur
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));

        return $this->redirectToRoute('home'); // Redirection vers la page d'accueil ou autre
    }

    


}