<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Authentication\ContributorAccount\Repository\InMemory;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;

class InMemoryRepository implements ContributorAccountRepository
{
    private array $contributorAccounts = [];

    public function save(ContributorAccount $contributorAccount): void
    {
        $this->contributorAccounts[(string) $contributorAccount->getEmail()] = $contributorAccount;
    }

    public function findByEmail(string $email): ?ContributorAccount
    {
        if (array_key_exists($email, $this->contributorAccounts)) {
            return $this->contributorAccounts[$email];
        }

        return null;
    }
}
