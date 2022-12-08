<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\EventAPI;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait AuthenticateAsTrait
{
    private function authenticateAs(string $username): UserInterface
    {
        if (false === $this instanceof TestCase) {
            throw new \LogicException();
        }

        /** @var UserRepositoryInterface */
        $userRepository = $this->get('pim_user.repository.user');

        /** @var TokenStorageInterface */
        $tokenStorage = $this->get('security.token_storage');

        $user = $userRepository->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage->setToken($token);

        return $user;
    }
}
