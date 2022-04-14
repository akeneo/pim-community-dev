<?php

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier;

interface GetAllSuppliersWithContributors
{
    public function __invoke(): array;
}
