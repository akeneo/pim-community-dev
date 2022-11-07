<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;

final class InMemoryGetProductFilePathAndFileName implements GetProductFilePathAndFileName
{
    public function __construct(private readonly ProductFileRepository $productFileRepository)
    {
    }

    public function __invoke(string $productFileIdentifier): ?ProductFilePathAndFileName
    {
        $productFile = $this->productFileRepository->find(Identifier::fromString($productFileIdentifier));

        if (null !== $productFile) {
            return new ProductFilePathAndFileName($productFile->originalFilename(), $productFile->path());
        }

        return null;
    }
}
