<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;

final class InMemoryGetProductFilePathAndFileName implements GetProductFilePathAndFileName
{
    public function __construct(private readonly ProductFileRepository $productFileRepository)
    {
    }

    public function __invoke(string $productFileIdentifier): ?ProductFilePathAndFileName
    {
        $productFile = $this->productFileRepository->find(Identifier::fromString($productFileIdentifier));

        if (null === $productFile) {
            return null;
        }

        return new ProductFilePathAndFileName($productFile->originalFilename(), $productFile->path());
    }
}
