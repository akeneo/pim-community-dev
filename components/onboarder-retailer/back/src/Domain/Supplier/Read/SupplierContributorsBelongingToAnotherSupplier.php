<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read;

interface SupplierContributorsBelongingToAnotherSupplier
{
    public function __invoke(string $supplierIdentifier, array $emails): array;
}
