<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithCommentsForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;

final class GetProductFileHandlerForSupplier
{
    public function __construct(private GetProductFileWithCommentsForSupplier $getProductFileWithCommentsForSupplier)
    {
    }

    public function __invoke(GetProductFileForSupplier $getProductFileForSupplier): ProductFile
    {
        $productFile = ($this->getProductFileWithCommentsForSupplier)($getProductFileForSupplier->productFileIdentifier);

        if (null === $productFile) {
            throw new ProductFileDoesNotExist();
        }

        return $productFile;
    }
}
