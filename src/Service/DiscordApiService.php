<?php

namespace App\Service;

use App\Model\DiscordUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DiscordApiService
{
    const AUTHORIZATION_BASE_URI = 'https://discord.com/api/oauth2/authorize';
    const API_VERSION = '10';
    const USERS_ME_ENDPOINT = 'https://discord.com/api/v' . self::API_VERSION . '/users/@me';

    public function __construct(
        private readonly HttpClientInterface $discordApiClient,
        private readonly SerializerInterface $serializer,
        private readonly string $clientId,
        private readonly string $redirectUri,
    ) {
    }

    public function getAuthorizationUrl(array $scope): string
    {
        return self::AUTHORIZATION_BASE_URI . "?" . http_build_query([
                'client_id' => $this->clientId,
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
                'scope' => implode(' ', $scope),
            ]);
    }

    public function fetchUser(string $accessToken): DiscordUser
    {
        $response = $this->discordApiClient->request(Request::METHOD_GET, self::USERS_ME_ENDPOINT, [
            'auth_bearer' => $accessToken
        ]);

        $data = $response->getContent();

        return $this->serializer->deserialize($data, DiscordUser::class, 'json');
    }
}
