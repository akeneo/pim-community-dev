<?php

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Domain\Read;

interface GetSupplier
{
    public function __invoke(Write\Supplier\ValueObject\Identifier $identifier): ?Read\Supplier\Model\Supplier;
}
