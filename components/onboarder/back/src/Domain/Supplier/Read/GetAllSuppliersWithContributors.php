<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Read;

interface GetAllSuppliersWithContributors
{
    public function __invoke(): array;
}
