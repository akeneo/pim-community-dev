<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Write\Event;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\ContributorAccount;

class ContributorAccountCreated
{
    public function __construct(public ContributorAccount $contributorAccount)
    {
    }
}