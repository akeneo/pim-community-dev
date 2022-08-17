<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\SupplierExists;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;

final class InMemorySupplierExists implements SupplierExists
{
    public function __construct(private InMemoryRepository $supplierRepository)
    {
    }

    public function fromCode(string $supplierCode): bool
    {
        return null !== $this->supplierRepository->findByCode($supplierCode);
    }
}
