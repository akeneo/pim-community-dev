<?php

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;

interface ContributorAccountRepository
{
    public function save(ContributorAccount $contributorAccount): void;
    public function find(Identifier $contributorAccountIdentifier): ?ContributorAccount;
}
