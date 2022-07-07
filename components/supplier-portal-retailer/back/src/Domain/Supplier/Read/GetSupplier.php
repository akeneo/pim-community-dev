<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;

interface GetSupplier
{
    public function __invoke(Identifier $identifier): ?SupplierWithContributors;
}
