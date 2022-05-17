<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Write;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\ContributorAccount;

interface ContributorAccountRepository
{
    public function save(ContributorAccount $contributorAccount): void;
}