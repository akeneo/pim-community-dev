<?php

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;

interface GetSupplier
{
    public function __invoke(Identifier $identifier): ?SupplierWithContributors;
}
