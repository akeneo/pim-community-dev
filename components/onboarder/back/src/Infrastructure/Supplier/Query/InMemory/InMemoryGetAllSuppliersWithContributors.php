<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetAllSuppliersWithContributors;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model\SupplierWithContributors;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
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
                $supplier->code(),
                $supplier->label(),
                $supplier->contributors(),
            ),
            $this->supplierRepository->findAll(),
        );
    }
}
