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

    public function deleteOld(): void
    {
        foreach ($this->supplierFiles as $supplierFile) {
            if (self::RETENTION_DURATION_IN_DAYS <
                (new \DateTimeImmutable($supplierFile->uploadedAt()))->diff(new \DateTimeImmutable())->days
            ) {
                unset($this->supplierFiles[$supplierFile->identifier()]);
            }
        }
    }
}
