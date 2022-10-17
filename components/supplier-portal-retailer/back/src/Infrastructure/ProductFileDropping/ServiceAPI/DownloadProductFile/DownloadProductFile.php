<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\SupplierDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;

final class DownloadProductFile
{
    public function __construct(
        private DownloadProductFileHandlerForSupplier $downloadProductFileHandler,
    ) {
    }

    /**
     * @throws Exception\ProductFileNotFound
     * @throws Exception\UnableToReadProductFile
     */
    public function __invoke(DownloadProductFileQuery $downloadProductFileQuery): ProductFile
    {
        try {
            $productFileNameAndResourceFile = ($this->downloadProductFileHandler)(
                new DownloadProductFileForSupplier(
                    $downloadProductFileQuery->productFileIdentifier,
                    $downloadProductFileQuery->contributorEmail,
                ),
            );
        } catch (ProductFileDoesNotExist | SupplierDoesNotExist $e) {
            throw new Exception\ProductFileNotFound(previous: $e);
        } catch (UnableToReadProductFile $e) {
            throw new Exception\UnableToReadProductFile(previous: $e);
        }

        return ProductFile::fromReadModel($productFileNameAndResourceFile);
    }
}
