<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Repository\InMemory;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;

class InMemoryRepository implements ContributorAccountRepository
{
    private array $contributorAccounts = [];

    public function save(ContributorAccount $contributorAccount): void
    {
        $this->contributorAccounts[$contributorAccount->email()] = $contributorAccount;
    }

    public function findByEmail(string $email): ?ContributorAccount
    {
        if (array_key_exists($email, $this->contributorAccounts)) {
            return $this->contributorAccounts[$email];
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
