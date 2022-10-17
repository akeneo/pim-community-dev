<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\SupplierDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\Exception\ProductFileNotFound;

final class DownloadProductFile
{
    public function __construct(
        private DownloadProductFileHandlerForSupplier $downloadProductFileHandler,
    ) {
    }

    public function __invoke(DownloadProductFileQuery $downloadProductFileQuery): ProductFile
    {
        try {
            $productFileNameAndResourceFile = ($this->downloadProductFileHandler)(
                new DownloadProductFileForSupplier($downloadProductFileQuery->productFileIdentifier, $downloadProductFileQuery->contributorEmail),
            );
        } catch (ProductFileDoesNotExist | ProductFileIsNotDownloadable | SupplierDoesNotExist) {
            throw new ProductFileNotFound();
        }

        return ProductFile::fromReadModel($productFileNameAndResourceFile);
    }
}
