<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFileNameAndResourceFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;
use Psr\Log\LoggerInterface;

final class DownloadProductFileHandler
{
    public function __construct(
        private DownloadStoredProductFile $downloadStoredProductsFile,
        private GetProductFilePathAndFileName $getProductFilePathAndFileName,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(DownloadProductFile $query): SupplierFileNameAndResourceFile
    {
        $productFilePathAndFileName = ($this->getProductFilePathAndFileName)(
            Identifier::fromString($query->productFileIdentifier)
        );

        if (empty($productFilePathAndFileName->path)) {
            throw new ProductFileDoesNotExist();
        }

        try {
            $productFileStream = ($this->downloadStoredProductsFile)(
                Path::fromString($productFilePathAndFileName->path)
            );
        } catch (\Throwable $e) {
            $this->logger->error('Product file could not be downloaded.', [
                'data' => [
                    'fileIdentifier' => $query->productFileIdentifier,
                    'path' => $productFilePathAndFileName->path,
                    'error' => $e->getMessage(),
                ],
            ]);
            throw new ProductFileIsNotDownloadable();
        }

        return new SupplierFileNameAndResourceFile($productFilePathAndFileName->originalFilename, $productFileStream);
    }
}
