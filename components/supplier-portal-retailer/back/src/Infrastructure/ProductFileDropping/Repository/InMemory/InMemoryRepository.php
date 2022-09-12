<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;

final class InMemoryRepository implements ProductFileRepository
{
    private array $productFiles = [];

    public function save(ProductFile $productFile): void
    {
        $this->productFiles[$productFile->identifier()] = $productFile;
    }

    public function findByContributor(ContributorEmail $uploadedByContributor): ?ProductFile
    {
        foreach ($this->productFiles as $productFile) {
            if ((string) $uploadedByContributor === $productFile->contributorEmail()) {
                return $productFile;
            }
        }

        return null;
    }

    public function find(Identifier $identifier): ?ProductFile
    {
        return $this->productFiles[(string) $identifier] ?? null;
    }
}
