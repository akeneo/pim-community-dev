<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Security;

use Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql\DatabaseGetContributorAccountByEmail;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class ContributorAccountProvider implements UserProviderInterface
{
    public function __construct(private DatabaseGetContributorAccountByEmail $getContributorAccountByEmail)
    {
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof ContributorAccount) {
            throw new UnsupportedUserException(
                sprintf('User object of class "%s" is not supported.', get_class($user)),
            );
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class)
    {
        return ContributorAccount::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return ($this->getContributorAccountByEmail)($identifier);
    }

    public function loadUserByUsername(string $username): ?UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }
}
