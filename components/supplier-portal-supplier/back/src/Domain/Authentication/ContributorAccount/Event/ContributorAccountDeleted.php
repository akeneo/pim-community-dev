<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event;

class ContributorAccountDeleted
{
    public function __construct(public string $contributorEmail)
    {
    }
}
