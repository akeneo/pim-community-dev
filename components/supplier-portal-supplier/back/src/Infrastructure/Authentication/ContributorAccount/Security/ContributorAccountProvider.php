<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql\DatabaseGetContributorAccountByEmail;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class ContributorAccountProvider implements UserProviderInterface
{
    public function __construct(private DatabaseGetContributorAccountByEmail $getContributorAccountByEmail)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof ContributorAccount) {
            throw new UnsupportedUserException(
                sprintf('User object of class "%s" is not supported.', get_class($user)),
            );
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return ContributorAccount::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = ($this->getContributorAccountByEmail)($identifier);

        if (null === $user) {
            $userNotFound = new UserNotFoundException();
            $userNotFound->setUserIdentifier($identifier);

            throw $userNotFound;
        }

        return $user;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }
}
