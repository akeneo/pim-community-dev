<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Repository\InMemory;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\SupplierFileRepository;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;

final class InMemoryRepository implements SupplierFileRepository
{
    private array $supplierFiles = [];

    public function save(SupplierFile $supplierFile): void
    {
        $this->supplierFiles[$supplierFile->uploadedByContributor()] = $supplierFile;
    }

    public function findByContributor(ContributorEmail $uploadedByContributor): ?SupplierFile
    {
        foreach ($this->supplierFiles as $supplierFile) {
            if ((string) $uploadedByContributor === $supplierFile->uploadedByContributor()) {
                return $supplierFile;
            }
        }

        return null;
    }
}
