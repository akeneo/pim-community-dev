<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event;

final class ContributorAccountDeleted
{
    public function __construct(public string $contributorEmail)
    {
    }
}
