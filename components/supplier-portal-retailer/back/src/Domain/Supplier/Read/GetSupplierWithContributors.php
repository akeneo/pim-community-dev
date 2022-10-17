<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;

interface GetSupplierWithContributors
{
    public function __invoke(string $identifier): ?SupplierWithContributors;
}
