<?php

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read;

interface GetAllSuppliersWithContributors
{
    public function __invoke(): array;
}
