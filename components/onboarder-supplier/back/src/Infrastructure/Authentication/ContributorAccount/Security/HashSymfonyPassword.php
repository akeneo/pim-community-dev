<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Security;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\HashPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HashSymfonyPassword implements HashPassword
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function __invoke(string $email, string $plainTextPassword): string
    {
        $contributorAccount = new ContributorAccount($email, $plainTextPassword);

        return $this->passwordHasher->hashPassword(
            $contributorAccount,
            $plainTextPassword,
        );
    }
}
