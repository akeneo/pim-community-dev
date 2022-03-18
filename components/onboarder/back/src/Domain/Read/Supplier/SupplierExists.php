<?php

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;

interface SupplierExists
{
    public function fromCode(Supplier\ValueObject\Code $supplierCode): bool;
}
