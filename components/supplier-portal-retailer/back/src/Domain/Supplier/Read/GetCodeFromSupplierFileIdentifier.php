<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

interface GetCodeFromSupplierFileIdentifier
{
    public function __invoke(string $supplierFileIdentifier): ?string;
}
