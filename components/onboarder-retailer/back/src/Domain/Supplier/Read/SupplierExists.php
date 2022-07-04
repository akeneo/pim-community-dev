<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;

interface SupplierExists
{
    public function fromCode(Code $supplierCode): bool;
}
