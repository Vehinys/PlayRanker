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
public function check(EntityManagerInterface $em, Request $request, UserRepository $userRepo): Response {
    $authorizationCode = $request->query->get('code');
    
    if (!$authorizationCode) {
        return $this->render('pages/security/check.html.twig', [
            'error' => 'No authorization code received from Discord.'
        ]);
    }

    $client = HttpClient::create();
    
        $response = $client->request('POST', 'https://discord.com/api/oauth2/token', [
            'body' => [
                'client_id' => 1298382523700609044,
                'client_secret' => 'k_1BNb9x502F1JMB9MIOvRMwtwX0Bqgg',
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'redirect_uri' => 'http://localhost:8000/discord/check',
            ],
        ]);

    $data = $response->toArray(false);

    if (isset($data['access_token'])) {
        $accessToken = $data['access_token'];

        // Use the access token to fetch the user from Discord API
        $discordUser = $this->discordApiService->fetchUser($accessToken);
        
        if (!$discordUser) {
            return $this->render('pages/security/check.html.twig', [
                'error' => 'Failed to retrieve user information from Discord.'
            ]);
        }

        $user = $userRepo->findOneBy(['discordId' => $discordUser->id]);

        if ($user) {
            $user->setAccessToken($accessToken);
            $em->flush();

            return $this->redirectToRoute('oauth_discord_auth', [
                'accessToken' => $accessToken,
            ]);
        } else {
            $user = new User();
            $user->setAccessToken($accessToken);
            $user->setUsername($discordUser->username);
            $user->setEmail($discordUser->email);
            $user->setAvatar($discordUser->avatar);
            $user->setDiscordId($discordUser->id);

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('oauth_discord_auth', [
                'accessToken' => $accessToken,
            ]);
        }
    } elseif (isset($data['error'])) {
        return $this->render('pages/security/check.html.twig', [
            'error' => 'Error: ' . $data['error']
        ]);
    } else {
        return $this->render('pages/security/check.html.twig', [
            'error' => 'Failed to retrieve access token from Discord.'
        ]);
    }
}

}