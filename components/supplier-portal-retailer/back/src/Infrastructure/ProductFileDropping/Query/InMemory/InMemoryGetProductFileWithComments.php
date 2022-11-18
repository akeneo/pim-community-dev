<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository;

final class InMemoryGetProductFileWithComments implements GetProductFileWithComments
{
    public function __construct(private InMemoryRepository $productFileRepository)
    {
    }

    public function __invoke(string $productFileIdentifier): ?ProductFile
    {
        $productFile = $this->productFileRepository->find(Identifier::fromString($productFileIdentifier));

        if (null === $productFile) {
            return null;
        }

        return new ProductFile(
            $productFile->identifier(),
            $productFile->originalFilename(),
            null,
            $productFile->contributorEmail(),
            $productFile->uploadedBySupplier(),
            $productFile->uploadedAt(),
            null,
            $productFile->newRetailerComments(),
            $productFile->newSupplierComments(),
        );
    }
}
