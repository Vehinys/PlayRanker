<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class DiscordAuthenticator extends AbstractAuthenticator
{

    const DISCORD_AUTH_KEY = 'discord-auth';

    public function __construct(
        
        private readonly UserRepository $userRepo, 
        private readonly RouterInterface $router)

    {
        
    }

    public function supports(
        
        Request $request
        
    ): ?bool {

        return $request->attributes->get('_route') == 'app_discord_auth' && $this->isValidRequest($request);
    }


    public function authenticate(Request $request): Passport
    {
        // Vérifie si la requête est valide
        if (!$this->isValidRequest($request)) {
            throw new AuthenticationException('Invalid Request');
        }

        // Récupère le token d'accès de la requête
        $accessToken = $request->query->get('access_token');
        if (!$accessToken) {
            throw new AuthenticationException('Access Token is missing');
        }

        // Rechercher l'utilisateur dans la base de données en fonction du token d'accès
        $user = $this->userRepo->findOneBy(['accessToken' => $accessToken]);

        if (!$user) {
            throw new AuthenticationException('Wrong access token');
        }

        // Création du UserBadge
        $userBadge = new UserBadge($user->getUserIdentifier(), function() use ($user) {
            return $user;
        });

        // Retourne un Passport validé
        return new SelfValidatingPassport($userBadge);
    }


    public function onAuthenticationSuccess(

        Request $request, 
        TokenInterface $token, 
        string $firewallName
        
    ): ?Response {

        // Supprime la clé d'authentification Discord de la session
        $request->getSession()->remove(self::DISCORD_AUTH_KEY);

        return null;
    }

    public function onAuthenticationFailure(

        Request $request, 
        AuthenticationException $exception
        
    ): ?Response {

        /**@var Session $session */
        $session = $request->getSession();
        $session->remove(self::DISCORD_AUTH_KEY);
        $session->getFlashBag()->set('danger',$exception->getMessage());

        return new RedirectResponse($this->router->generate('login'));
        
    }

    private function isValidRequest(
        
        Request $request
        
    ): bool {

        // Vérifie si la clé de session existe et si sa valeur n'est pas nulle
        return $request->getSession()->has(self::DISCORD_AUTH_KEY) && 
        $request->getSession()->get(self::DISCORD_AUTH_KEY) !== null;
    }
}