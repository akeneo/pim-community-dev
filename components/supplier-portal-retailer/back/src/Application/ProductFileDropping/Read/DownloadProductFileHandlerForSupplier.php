<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\SupplierDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileNameForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Psr\Log\LoggerInterface;

final class DownloadProductFileHandlerForSupplier
{
    public function __construct(
        private GetProductFilePathAndFileNameForSupplier $getProductFilePathAndFileName,
        private DownloadStoredProductFile $downloadStoredProductFile,
        private GetSupplierFromContributorEmail $getSupplierFromContributorEmail,
        private LoggerInterface $logger,
    ) {
    }

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

        try {
            // FlySystem can throw exceptions that do not extend \Exception but \Throwable, which is one level higher
            $productFileStream = ($this->downloadStoredProductFile)($productFilePathAndFileName->path);
        } catch (\Throwable $e) {
            $this->logger->error('Product file could not be downloaded', [
                'data' => [
                    'fileIdentifier' => $query->productFileIdentifier,
                    'filename' => $productFilePathAndFileName->originalFilename,
                    'path' => $productFilePathAndFileName->path,
                    'error' => $e->getMessage(),
                ],
            ]);
            throw new ProductFileIsNotDownloadable();
        }

        return new ProductFileNameAndResourceFile($productFilePathAndFileName->originalFilename, $productFileStream);
    }
}
