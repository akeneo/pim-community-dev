<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Repository\InMemory;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\SupplierFileRepository;

final class InMemoryRepository implements SupplierFileRepository
{
    private array $supplierFiles = [];

    public function save(SupplierFile $supplierFile): void
    {
        $this->supplierFiles[$supplierFile->identifier()] = $supplierFile;
    }
}
