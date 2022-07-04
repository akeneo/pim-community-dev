<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

interface GetAllSuppliersWithContributors
{
    public function __invoke(): array;
}
