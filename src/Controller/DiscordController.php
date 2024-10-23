<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DiscordApiService;
use App\Security\DiscordAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class DiscordController extends AbstractController 

{

    public function __construct(
        private readonly DiscordApiService $discordApiService
    ) {

    }

    #[Route('/discord/auth', name: 'app_discord_auth')]
    public function auth(

    ): Response {

        return $this->redirectToRoute("home");
    }
    
    // ---------------------------------------------------------- //
    // Méthode pour gérer la connexion via Discord
    // ---------------------------------------------------------- //

    #[Route('/discord/connect', name: 'app_discord_connect')]
    public function connect(
        
        Request $request,
        AuthenticationUtils $authenticationUtils
        
    ): Response {

        // Récupère l'erreur d'authentification s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();
        
        $token = $request->request->get('token');

        if ($this->isCsrfTokenValid('discord-auth', $token)) {

            $request->getSession()->set(DiscordAuthenticator::DISCORD_AUTH_KEY, true);
            $scope = ['identify', 'email'];

            return $this->redirect($this->discordApiService->getAuthorizationUrl($scope));
        }

        return $this->render('pages/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }


    // ---------------------------------------------------------- //
    // Méthode de vérification par check
    // ---------------------------------------------------------- //

    #[Route('/discord/check', name: 'app_discord_check')]
    public function check(

        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo
        
    ): Response {

        $accessToken = $request->get('access_token');

        dd($accessToken);

        if (!$accessToken) {
            return $this->render('/pages/security/check.html.twig');
        }

        $discordUser = $this->discordApiService->fetchUser($accessToken);
        
        $user = $userRepo->findOneBy(['discordId'=> $discordUser->id]);



        if ($user) {
           return $this->redirectToRoute('app_discord_auth', [
            'accessToken' => $accessToken
           ]);
        }

        $user = new User();
        $user->setAccessToken($accessToken);
        $user->setUsername($discordUser->username);
        $user->setEmail($discordUser->email);
        $user->setAvatar($discordUser->avatar);
        $user->setDiscordId($discordUser->id);

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_discord_auth', [
            'accessToken' => $accessToken
        ]);
    }
}
