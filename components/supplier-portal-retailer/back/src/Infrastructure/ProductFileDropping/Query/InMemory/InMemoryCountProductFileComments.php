<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountProductFileComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository;

final class InMemoryCountProductFileComments implements CountProductFileComments
{
    public function __construct(private InMemoryRepository $productFileRepository)
    {
    }

    public function save(ProductFile $productFile): void
    {
        $this->productFileRepository->save($productFile);
    }

    public function __invoke(string $productFileIdentifier): int
    {
        $productFile = $this->productFileRepository->find(Identifier::fromString($productFileIdentifier));
        if (null === $productFile) {
            return 0;
        }

        return \count($productFile->newRetailerComments()) + \count($productFile->newSupplierComments());
    }
}
