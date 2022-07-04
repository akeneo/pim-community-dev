<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount;

class CreateContributorAccount
{
    public function __construct(public string $contributorEmail)
    {
    }
}
