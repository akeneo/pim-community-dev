<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePath;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;

final class DownloadProductFileHandler
{
    public function __construct(
        private GetProductFilePath $getProductFilePath,
        private DownloadStoredProductFile $downloadStoredProductsFile,
        private LoggerInterface $logger,
    ) {
    }

    //@phpstan-ignore-next-line
    public function __invoke(DownloadProductFile $query)
    {
        $productFilePath = ($this->getProductFilePath)(Identifier::fromString($query->productFileIdentifier));
        if (null === $productFilePath) {
            throw new ProductFileDoesNotExist();
        }

        try {
            $productFileStream = ($this->downloadStoredProductsFile)($productFilePath);
        } catch (\Throwable $e) {
            $this->logger->error('Product file could not be downloaded.', [
                'data' => [
                    'fileIdentifier' => $query->productFileIdentifier,
                    'path' => $productFilePath,
                    'error' => $e->getMessage(),
                ],
            ]);
            throw new ProductFileIsNotDownloadable();
        }

        return $productFileStream;
    }
}
