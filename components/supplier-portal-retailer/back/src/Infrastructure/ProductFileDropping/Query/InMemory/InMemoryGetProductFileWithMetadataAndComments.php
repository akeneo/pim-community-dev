<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithMetadataAndComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithMetadataAndComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository;

final class InMemoryGetProductFileWithMetadataAndComments implements GetProductFileWithMetadataAndComments
{
    public function __construct(private InMemoryRepository $productFileRepository)
    {
    }

    public function __invoke(string $productFileIdentifier): ?ProductFileWithMetadataAndComments
    {
        $productFile = $this->productFileRepository->find(Identifier::fromString($productFileIdentifier));

        if (null === $productFile) {
            return null;
        }

        return new ProductFileWithMetadataAndComments(
            $productFile->identifier(),
            $productFile->originalFilename(),
            null,
            $productFile->contributorEmail(),
            $productFile->uploadedBySupplier(),
            $productFile->uploadedAt(),
            null,
            null,
            null,
            $productFile->newRetailerComments(),
            $productFile->newSupplierComments(),
        );
    }
}
