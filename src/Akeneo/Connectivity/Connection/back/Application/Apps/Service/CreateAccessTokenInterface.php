<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

interface CreateAccessTokenInterface
{
    /**
     *
     * @throws \InvalidArgumentException
     *
     * @return array{access_token: string, token_type: string}
     */
    public function create(string $appId, string $authCode): array;
}
