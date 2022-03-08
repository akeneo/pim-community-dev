<?php

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier;

interface GetSupplierCount
{
    public function __invoke(string $search = ''): int;
}
