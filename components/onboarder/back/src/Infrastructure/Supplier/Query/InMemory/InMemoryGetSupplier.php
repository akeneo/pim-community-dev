<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Read;
use Akeneo\OnboarderSerenity\Domain\Supplier\Read\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemoryGetSupplier implements GetSupplier
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(Write\Supplier\ValueObject\Identifier $identifier): ?Read\Supplier\Model\SupplierWithContributors
    {
        $supplier = $this->supplierRepository->find($identifier);

        if (null === $supplier) {
            return null;
        }

        return new Read\Supplier\Model\SupplierWithContributors(
            $supplier->identifier(),
            $supplier->code(),
            $supplier->label(),
            $supplier->contributors(),
        );
    }
}
