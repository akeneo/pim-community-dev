<?php

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Code;

interface SupplierExists
{
    public function fromCode(Code $supplierCode): bool;
}
