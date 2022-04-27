<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;

final class DeleteSupplierHandler
{
    public function __construct(
        private Repository $supplierRepository,
    ) {
    }

    public function __invoke(DeleteSupplier $deleteSupplier): void
    {
        $this->supplierRepository->delete(
            Identifier::fromString($deleteSupplier->identifier),
        );
    }
}
