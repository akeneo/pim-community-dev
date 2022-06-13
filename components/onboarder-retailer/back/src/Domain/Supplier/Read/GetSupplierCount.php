<?php

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read;

interface GetSupplierCount
{
    public function __invoke(string $search = ''): int;
}
