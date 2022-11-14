<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemoryGetSupplierWithContributors implements GetSupplierWithContributors
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(string $identifier): ?SupplierWithContributors
    {
        $supplier = $this->supplierRepository->find(Identifier::fromString($identifier));

        if (null === $supplier) {
            return null;
        }

        return new SupplierWithContributors(
            $supplier->identifier(),
            $supplier->code(),
            $supplier->label(),
            array_map(fn (array $contributorEmail) => $contributorEmail['email'], $supplier->contributors()),
        );
    }
}
