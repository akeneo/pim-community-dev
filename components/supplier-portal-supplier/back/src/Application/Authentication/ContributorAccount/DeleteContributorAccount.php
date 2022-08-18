<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount;

class DeleteContributorAccount
{
    public function __construct(public string $contributorEmail)
    {
    }
}
