<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Repository\InMemory;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;

class InMemoryRepository implements ContributorAccountRepository
{
    private array $contributorAccounts = [];

    public function save(ContributorAccount $contributorAccount): void
    {
        $this->contributorAccounts[$contributorAccount->email()] = $contributorAccount;
    }

    public function findByEmail(Email $email): ?ContributorAccount
    {
        if (array_key_exists((string) $email, $this->contributorAccounts)) {
            return $this->contributorAccounts[(string) $email];
        }

        return null;
    }

    public function find(Identifier $identifier): ?ContributorAccount
    {
        foreach ($this->contributorAccounts as $contributorAccount) {
            if ((string) $identifier === $contributorAccount->identifier()) {
                return $contributorAccount;
            }
        }

        return null;
    }
}
