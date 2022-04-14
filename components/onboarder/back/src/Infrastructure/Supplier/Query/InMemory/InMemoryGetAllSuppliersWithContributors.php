<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\GetAllSuppliersWithContributors;
use Akeneo\OnboarderSerenity\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

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
