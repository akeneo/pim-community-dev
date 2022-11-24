<?php

namespace Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\CreateContributorAccount;

class CreateContributorAccount
{
    public function __construct(public string $contributorEmail, public \DateTimeImmutable $createdAt)
    {
    }
}
