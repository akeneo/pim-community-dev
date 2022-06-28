<?php

namespace Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount;

class CreateContributorAccount
{
    public function __construct(public string $contributorEmail)
    {
    }
}
