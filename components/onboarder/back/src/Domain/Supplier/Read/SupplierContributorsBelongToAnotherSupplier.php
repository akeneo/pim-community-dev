<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Read;

interface SupplierContributorsBelongToAnotherSupplier
{
    public function __invoke(string $supplierIdentifier, array $emails): array;
}
