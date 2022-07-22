<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Repository\InMemory;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\SupplierFileRepository;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;

final class InMemoryRepository implements SupplierFileRepository
{
    private array $supplierFiles = [];

    public function save(SupplierFile $supplierFile): void
    {
        $this->supplierFiles[$supplierFile->identifier()] = $supplierFile;
    }

    public function find(Identifier $identifier): ?SupplierFile
    {
        foreach ($this->supplierFiles as $supplierFile) {
            if ((string) $identifier === $supplierFile->identifier()) {
                return $supplierFile;
            }
        }

        return null;
    }
}
