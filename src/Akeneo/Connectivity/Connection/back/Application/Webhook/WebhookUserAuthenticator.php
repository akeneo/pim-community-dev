<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookUserAuthenticator
{
    public function __construct(private UserRepositoryInterface $userRepository, private TokenStorageInterface $tokenStorage)
    {
    }

    public function authenticate(int $userId): UserInterface
    {
        /** @var ?UserInterface $user */
        $user = $this->userRepository->find($userId);
        if (null === $user) {
            throw new \RuntimeException(\sprintf('User "%s" not found', $userId));
        }

        $roles = \array_map('strval', $user->getRoles());

        $this->tokenStorage->setToken(new UsernamePasswordToken($user, 'main', $roles));

        return $user;
    }
}
