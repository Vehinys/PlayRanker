<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class DiscordAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return false;
    }

    public function authenticate(

        Request $request
        
    ): Passport {

        //Todo
    }

    public function onAuthenticationSuccess(

        Request $request, 
        TokenInterface $token, 
        string $firewallName
        
    ): ?Response {

        // Todo
    }

    public function onAuthenticationFailure(

        Request $request, 
        AuthenticationException $exception
        
    ): ?Response {

        //Todo
        
    }
}