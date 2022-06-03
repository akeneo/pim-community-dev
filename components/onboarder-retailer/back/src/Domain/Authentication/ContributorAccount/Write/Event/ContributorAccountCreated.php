<?php

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Event;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;

class ContributorAccountCreated
{
    public function __construct(public ContributorAccount $contributorAccount)
    {
    }
}
