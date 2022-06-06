<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\GetAllSuppliersWithContributors;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemoryGetAllSuppliersWithContributors implements GetAllSuppliersWithContributors
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(): array
    {
        return array_map(
            fn (Supplier $supplier) => new SupplierWithContributors(
                $supplier->identifier(),
                $supplier->code(),
                $supplier->label(),
                $supplier->contributors(),
            ),
            $this->supplierRepository->findAll(),
        );
    }
}
