<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\ContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;

final class InMemoryRepository implements ProductFileRepository
{
    private array $productFiles = [];
    private array $productFilesCommentsLastReadDateForSupplier = [];
    private array $productFilesCommentsLastReadDateForRetailer = [];

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

    public function findByName(string $fileName): ?ProductFile
    {
        foreach ($this->productFiles as $productFile) {
            if ($productFile->originalFilename() === $fileName) {
                return $productFile;
            }
        }

        return null;
    }

    public function updateProductFileLastReadAtDateForRetailer(Identifier $identifier, \DateTimeImmutable $date): void
    {
        $this->productFilesCommentsLastReadDateForRetailer[(string) $identifier] = $date;
    }

    public function updateProductFileLastReadAtDateForSupplier(Identifier $identifier, \DateTimeImmutable $date): void
    {
        $this->productFilesCommentsLastReadDateForSupplier[(string) $identifier] = $date;
    }

    public function findProductFileWithUnreadCommentsFromRetailer(Identifier $identifier): ?array
    {
        foreach ($this->productFiles as $productFile) {
            if ((string) $productFile->identifier() === (string) $identifier) {
                return [
                    'productFile' => $productFile,
                    'commentslastReadDate' => $this->productFilesCommentsLastReadDateForSupplier[(string) $productFile->identifier()] ?? null,
                ];
            }
        }

        return null;
    }

    public function findProductFileWithUnreadCommentsFromSupplier(Identifier $identifier): ?array
    {
        foreach ($this->productFiles as $productFile) {
            if ((string) $productFile->identifier() === (string) $identifier) {
                return [
                    'productFile' => $productFile,
                    'commentslastReadDate' => $this->productFilesCommentsLastReadDateForRetailer[(string) $productFile->identifier()] ?? null,
                ];
            }
        }

        return null;
    }

    public function deleteProductFileRetailerComments(string $productFileIdentifier): void
    {
        // Not implemented yet
    }

    public function deleteProductFileSupplierComments(string $productFileIdentifier): void
    {
        // Not implemented yet
    }

    public function deleteOldProductFiles(): void
    {
        // Not implemented yet
    }
}
