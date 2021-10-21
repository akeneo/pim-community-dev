<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use OAuth2\IOAuth2;
use OAuth2\IOAuth2GrantCode;
use OAuth2\OAuth2;

class CreateAccessToken implements CreateAccessTokenInterface
{
    private IOAuth2 $auth2;
    private IOAuth2GrantCode $storage;

    public function __construct(IOAuth2 $auth2, IOAuth2GrantCode $storage)
    {
        $this->auth2 = $auth2;
        $this->storage = $storage;
    }

    public function create(string $clientId, string $code): array
    {
        $client = $this->storage->getClient($clientId);
        if (null === $client) {
            throw new \InvalidArgumentException('No client found with the given client id.');
        }
        $authCode = $this->storage->getAuthCode($code);
        if (null === $authCode) {
            throw new \InvalidArgumentException('Unknown authorization code.');
        }

        return $this->auth2->createAccessToken(
            $client,
            $authCode->getData(),
            $authCode->getScope(),
            $this->auth2->getVariable(OAuth2::CONFIG_ACCESS_LIFETIME),
            true,
            $this->auth2->getVariable(OAuth2::CONFIG_REFRESH_LIFETIME)
        );
    }
}
