<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write;

class CreateContributorAccount
{
    public function __construct(public string $contributorEmail)
    {
    }
}
