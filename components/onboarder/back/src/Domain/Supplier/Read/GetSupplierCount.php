<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Read;

interface GetSupplierCount
{
    public function __invoke(string $search = ''): int;
}
