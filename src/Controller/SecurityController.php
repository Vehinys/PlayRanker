<?php

namespace App\Controller;

use App\Service\DiscordApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    
    // ---------------------------------------------------------- //
    // Méthode pour afficher le formulaire de connexion
    // ---------------------------------------------------------- //

    #[Route(path: '/login', name: 'login')]
    public function login(

        AuthenticationUtils $authenticationUtils

    ): Response {
        
        // Récupère l'erreur d'authentification s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affiche le formulaire de connexion avec l'erreur et le dernier nom d'utilisateur
        return $this->render('pages/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    
    // ---------------------------------------------------------- //
    // Méthode pour gérer la déconnexion
    // Cette méthode est interceptée par le système de sécurité
    // ---------------------------------------------------------- //

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function __construct(

        private readonly DiscordApiService $discordApiService,
    ) {

        
    }


    #[Route('/discord/connect', name: 'app_discord_connect')]
    public function connect(

        Request $request

    ): Response {

        $token = $request->request->get('token' );

        if ($this ->isCsrfTokenValid('discord-auth', $token )) {
        $request->getSession()->set( 'discord-auth', true );
        $scope = ['identify', 'email'];

        return $this->redirect($this->discordApiService->getAuthorizationUrl($scope));

        }

        return $this->redirectToRoute('login');

    }

    #[Route('/discord/check', name: 'app_discord_check')]
    public function check(

    Request $request

    ): Response {

        $accessToken = $request->get('access_token');

        if (!$accessToken) {
            return $this->render('/pages/security/check.html.twig');
            }

        }

    }


}
