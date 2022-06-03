<?php

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;

interface ContributorAccountRepository
{
    public function save(ContributorAccount $contributorAccount): void;
}
