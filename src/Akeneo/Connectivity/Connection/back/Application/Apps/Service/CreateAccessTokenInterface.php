<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

interface CreateAccessTokenInterface
{
    /**
     * @param string $clientId
     * @param string $authCode
     *
     * @return array
     *
     * TODO PHPSTAN
     * "access_token" => $this->genAccessToken(),
    "expires_in" => ($access_token_lifetime ?: $this->getVariable(self::CONFIG_ACCESS_LIFETIME)),
    "token_type" => $this->getVariable(self::CONFIG_TOKEN_TYPE),
    "scope" => $scope,
     */
    public function create(string $clientId, string $authCode): array;
}
