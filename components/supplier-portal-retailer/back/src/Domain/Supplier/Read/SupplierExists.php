<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

interface SupplierExists
{
    public function fromCode(string $supplierCode): bool;
}
