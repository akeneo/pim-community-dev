<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

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
