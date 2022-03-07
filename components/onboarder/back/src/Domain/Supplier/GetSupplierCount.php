<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier;

interface GetSupplierCount
{
    public function __invoke(string $search = ''): int;
}
