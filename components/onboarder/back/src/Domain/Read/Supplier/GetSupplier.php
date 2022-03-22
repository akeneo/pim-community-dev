<?php

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;

interface GetSupplier
{
    public function __invoke(Supplier\ValueObject\Code $code): ?Supplier\Model\Supplier;
}
