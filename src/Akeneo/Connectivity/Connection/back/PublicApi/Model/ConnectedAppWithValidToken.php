<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\PublicApi\Model;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectedAppWithValidToken
{
    public function __construct(
        private string $id,
        private string $code,
        private UserInterface $user,
        private string $userGroupName,
        private string $userRole,
        private string $accessToken,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getUserGroupName(): string
    {
        return $this->userGroupName;
    }

    public function getUserRole(): string
    {
        return $this->userRole;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
