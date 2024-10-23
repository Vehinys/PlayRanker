<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DiscordApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiscordController extends AbstractController
{
    public function __construct(
        private readonly DiscordApiService $discordApiService
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

    #[Route('/discord/check', name: 'oauth_discord_check')]
    public function check(EntityManagerInterface $em, Request $request, UserRepository $userRepo): Response
    {
        $accessToken = $request->query->get('access_token'); // Assurez-vous d'utiliser query

        dd($accessToken); // Vérifiez ici si le token est correctement récupéré

        if (!$accessToken) {
            return $this->render('pages/security/check.html.twig');
        }

        // Récupération des données utilisateur via Discord
        try {
            $discordUser = $this->discordApiService->fetchUser($accessToken);
        } catch (\Exception $e) {
            // Gérer l'erreur de récupération des données
            return new Response('Erreur lors de la récupération des données utilisateur: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        // Vérification si l'utilisateur existe déjà
        $user = $userRepo->findOneBy(['discordId' => $discordUser->id]);

        if ($user) {
            return $this->redirectToRoute('oauth_discord_auth', [
                'accessToken' => $accessToken
            ]);
        }

        // Création d'un nouvel utilisateur
        $user = new User();
        $user->setAccessToken($accessToken);
        $user->setUsername($discordUser->username);
        $user->setEmail($discordUser->email);
        $user->setAvatar($discordUser->avatar);
        $user->setDiscordId($discordUser->id);

        // Enregistrement en base de données
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('oauth_discord_auth', [
            'accessToken' => $accessToken
        ]);
    }

}