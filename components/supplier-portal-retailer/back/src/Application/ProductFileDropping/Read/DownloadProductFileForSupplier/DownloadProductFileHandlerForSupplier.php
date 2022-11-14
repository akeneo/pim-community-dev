<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileForSupplier;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StreamStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileNameForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Exception\SupplierDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;

class DownloadProductFileHandlerForSupplier
{
    public function __construct(
        private GetProductFilePathAndFileNameForSupplier $getProductFilePathAndFileName,
        private StreamStoredProductFile $streamStoredProductFile,
        private GetSupplierFromContributorEmail $getSupplierFromContributorEmail,
    ) {
    }

    /**
     * @throws UnableToReadProductFile
     * @throws SupplierDoesNotExist
     * @throws ProductFileDoesNotExist
     */
    public function __invoke(DownloadProductFileForSupplier $query): ProductFileNameAndResourceFile
    {
        $supplier = ($this->getSupplierFromContributorEmail)($query->contributorEmail);

        if (null === $supplier) {
            throw new SupplierDoesNotExist();
        }

        $productFilePathAndFileName = ($this->getProductFilePathAndFileName)($query->productFileIdentifier, $supplier->identifier);
        if (null === $productFilePathAndFileName) {
            throw new ProductFileDoesNotExist();
        }

        $productFileStream = ($this->streamStoredProductFile)($productFilePathAndFileName->path);

        return new ProductFileNameAndResourceFile($productFilePathAndFileName->originalFilename, $productFileStream);
    }
}
