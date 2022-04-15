<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Read;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code;

interface SupplierExists
{
    public function fromCode(Code $supplierCode): bool;
}
