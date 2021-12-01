<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2Client;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationCodeGenerator implements AuthorizationCodeGeneratorInterface
{
    private const AUTH_CODE_LIFETIME = 30; // 30 seconds

    public function __construct(
        private ClientManagerInterface $clientManager,
        private UserRepositoryInterface $userRepository,
        private IOAuth2GrantCode $storage,
        private RandomCodeGeneratorInterface $randomCodeGenerator,
        private Clock $clock
    ) {
    }

    public function generate(
        AppConfirmation $appConfirmation,
        int $userId,
        string $redirectUriWithoutCode,
        string $scope
    ): string {
        $code = $this->randomCodeGenerator->generate();
        $client = $this->findFosClient($appConfirmation->getFosClientId());
        $user = $this->findUser($userId);

        $this->storage->createAuthCode(
            $code,
            $client,
            $user,
            $redirectUriWithoutCode,
            $this->clock->now()->getTimestamp() + self::AUTH_CODE_LIFETIME,
            $scope
        );

        return $code;
    }

    private function findUser(int $userId): UserInterface
    {
        $user = $this->userRepository->find($userId);

        if (null === $user) {
            throw new \InvalidArgumentException(sprintf('User with id "%s" does not exist.', $userId));
        }

        return $user;
    }

    private function findFosClient(int $fosClientId): IOAuth2Client
    {
        $client = $this->clientManager->findClientBy(['id' => $fosClientId]);

        if (null === $client) {
            throw new \InvalidArgumentException(sprintf('FOS Client with id "%s" does not exist.', $fosClientId));
        }

        return $client;
    }
}
