<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

interface GetSupplierCodeFromSupplierFileIdentifier
{
    public function __invoke(string $supplierFileIdentifier): ?string;
}
