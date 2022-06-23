<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\Model;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class ContributorAccountUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(private string $email, private string $password)
    {
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getSalt()
    {

    }

    public function eraseCredentials()
    {

    }
}
