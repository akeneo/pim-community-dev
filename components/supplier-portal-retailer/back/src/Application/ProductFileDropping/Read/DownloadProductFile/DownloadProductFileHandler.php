<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StreamStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;

final class DownloadProductFileHandler
{
    public function __construct(
        private GetProductFilePathAndFileName $getProductFilePathAndFileName,
        private StreamStoredProductFile $streamStoredProductFile,
    ) {
    }

    /**
     * @throws UnableToReadProductFile
     * @throws ProductFileDoesNotExist
     * @phpstan-ignore-next-line
     */
    public function __invoke(DownloadProductFile $query)
    {
        $productFilePathAndFileName = ($this->getProductFilePathAndFileName)($query->productFileIdentifier);
        if (null === $productFilePathAndFileName) {
            throw new ProductFileDoesNotExist();
        }

        $productFileStream = ($this->streamStoredProductFile)($productFilePathAndFileName->path);

        return new ProductFileNameAndResourceFile($productFilePathAndFileName->originalFilename, $productFileStream);
    }
}
