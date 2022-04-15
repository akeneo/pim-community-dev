<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Read;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;

interface GetSupplier
{
    public function __invoke(Identifier $identifier): ?SupplierWithContributors;
}
