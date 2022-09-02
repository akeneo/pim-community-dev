<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\ServiceApi\Model;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectedAppWithValidToken
{
    public function __construct(
        private string $id,
        private string $username,
        private string $accessToken,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
