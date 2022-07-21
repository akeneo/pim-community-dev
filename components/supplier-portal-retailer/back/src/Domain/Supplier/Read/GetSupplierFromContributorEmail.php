<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;

interface GetSupplierFromContributorEmail
{
    public function __invoke(string $contributorEmail): ?Supplier;
}
