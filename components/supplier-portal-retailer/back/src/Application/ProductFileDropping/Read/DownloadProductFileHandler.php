<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;
use Psr\Log\LoggerInterface;

final class DownloadProductFileHandler
{
    public function __construct(
        private GetProductFilePathAndFileName $getProductFilePathAndFileName,
        private DownloadStoredProductFile $downloadStoredProductFile,
        private LoggerInterface $logger,
    ) {
    }

    //@phpstan-ignore-next-line
    public function __invoke(DownloadProductFile $query)
    {
        $productFilePathAndFileName = ($this->getProductFilePathAndFileName)($query->productFileIdentifier);
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
