<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Security;

use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JobUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->userRepository->findOneByIdentifier($username);
        if (!$user || $user->isApiUser()) {
            throw new UserNotFoundException(sprintf('User with username "%s" does not exist.', $username));
        }

        if (!$user->isEnabled()) {
            throw new DisabledException('User account is disabled.');
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass($user::class)) {
            throw new UnsupportedUserException(sprintf('User object of class "%s" is not supported.', $user::class));
        }

        $reloadedUser = $this->userRepository->find($user->getId());
        if (null === $reloadedUser || $reloadedUser->isApiUser()) {
            throw new UserNotFoundException(sprintf('User with id %d not found', $user->getId()));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, 'Akeneo\UserManagement\Component\Model\UserInterface');
    }
}
