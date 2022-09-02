<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\SupplierFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;

final class InMemoryRepository implements SupplierFileRepository
{
    private array $supplierFiles = [];

    public function save(SupplierFile $supplierFile): void
    {
        $this->supplierFiles[$supplierFile->identifier()] = $supplierFile;
    }

    public function findByContributor(ContributorEmail $uploadedByContributor): ?SupplierFile
    {
        foreach ($this->supplierFiles as $supplierFile) {
            if ((string) $uploadedByContributor === $supplierFile->contributorEmail()) {
                return $supplierFile;
            }
        }

        return null;
    }
}
