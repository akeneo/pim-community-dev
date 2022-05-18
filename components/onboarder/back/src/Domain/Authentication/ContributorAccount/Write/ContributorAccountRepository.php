<?php

namespace Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;

interface ContributorAccountRepository
{
    public function save(ContributorAccount $contributorAccount): void;
}
