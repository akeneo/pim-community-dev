<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

interface GetSupplierCodeFromProductFileIdentifier
{
    public function __invoke(string $productFileIdentifier): ?string;
}
