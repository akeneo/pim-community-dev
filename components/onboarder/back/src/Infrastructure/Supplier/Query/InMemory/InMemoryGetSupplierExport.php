<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierExport;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model\SupplierExport;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemoryGetSupplierExport implements GetSupplierExport
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function __invoke(): array
    {
        return array_map(
            fn (Supplier $supplier) => new SupplierExport(
                $supplier->code(),
                $supplier->label(),
                $supplier->contributors(),
            ),
            $this->supplierRepository->findAll(),
        );
    }
}
