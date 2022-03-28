<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Domain\Read;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemoryGetSupplier implements GetSupplier
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(Write\Supplier\ValueObject\Identifier $identifier): ?Read\Supplier\Model\Supplier
    {
        $supplier = $this->supplierRepository->getByIdentifier($identifier);

        if ($supplier === null) {
            return null;
        }

        return new Read\Supplier\Model\Supplier(
            $supplier->identifier(),
            $supplier->code(),
            $supplier->label(),
            $supplier->contributors()->toArray()
        );
    }
}
