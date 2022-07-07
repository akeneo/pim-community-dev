<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Event;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;

class ContributorAccountCreated
{
    public function __construct(public ContributorAccount $contributorAccount)
    {
    }
}
