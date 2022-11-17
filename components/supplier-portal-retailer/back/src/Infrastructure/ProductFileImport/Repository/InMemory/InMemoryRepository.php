<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Repository\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;

final class InMemoryRepository implements ProductFileImportRepository
{
    private array $productFileImports = [];

    public function save(ProductFileImport $productFileImport): void
    {
        $this->productFileImports[$productFileImport->productFileIdentifier()] = $productFileImport;
    }

    public function find(string $productFileIdentifier): ?ProductFileImport
    {
        return $this->productFileImports[$productFileIdentifier] ?? null;
    }

    public function findByImportExecutionId(int $importExecutionId): ?ProductFileImport
    {
        foreach ($this->productFileImports as $productFileImport) {
            if ($importExecutionId === $productFileImport->importExecutionId()) {
                return $productFileImport;
            }
        }

        return null;
    }
}
