<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\GetSupplier;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemoryGetSupplier implements GetSupplier
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(Identifier $identifier): ?SupplierWithContributors
    {
        $supplier = $this->supplierRepository->find($identifier);

        if (null === $supplier) {
            return null;
        }

        return new SupplierWithContributors(
            $supplier->identifier(),
            $supplier->code(),
            $supplier->label(),
            $supplier->contributors(),
        );
    }
}
