<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;

interface GetSupplierWithContributors
{
    public function __invoke(Identifier $identifier): ?SupplierWithContributors;
}
