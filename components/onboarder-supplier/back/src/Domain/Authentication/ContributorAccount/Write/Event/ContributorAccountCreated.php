<?php

namespace Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Event;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;

class ContributorAccountCreated
{
    public function __construct(public ContributorAccount $contributorAccount)
    {
    }
}
