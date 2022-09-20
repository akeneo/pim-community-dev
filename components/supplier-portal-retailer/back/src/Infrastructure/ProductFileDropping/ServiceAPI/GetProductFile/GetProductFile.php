<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\GetProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\GetProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\Exception\ProductFileNotFound;

final class GetProductFile
{
    public function __construct(
        private GetProductFileHandlerForSupplier $getProductFileHandlerForSupplier,
    ) {
    }

    public function __invoke(GetProductFileQuery $getProductFileQuery): ProductFile
    {
        try {
            $productFile = ($this->getProductFileHandlerForSupplier)(
                new GetProductFileForSupplier(
                    $getProductFileQuery->productFileIdentifier,
                )
            );
        } catch (ProductFileDoesNotExist) {
            throw new ProductFileNotFound();
        }

        return ProductFile::fromReadModel($productFile);
    }
}
