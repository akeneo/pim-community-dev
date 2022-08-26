<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierCodeFromSupplierFileIdentifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DownloadProductFileHandler
{
    public function __construct(
        private GetProductFilePathAndFileName $getProductFilePathAndFileName,
        private DownloadStoredProductFile $downloadStoredProductFile,
        private EventDispatcherInterface $eventDispatcher,
        private GetSupplierCodeFromSupplierFileIdentifier $getSupplierCodeFromSupplierFileIdentifier,
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
            $this->logger->error('Supplier file could not be downloaded', [
                'data' => [
                    'fileIdentifier' => $query->productFileIdentifier,
                    'filename' => $productFilePathAndFileName->originalFilename,
                    'path' => $productFilePathAndFileName->path,
                    'error' => $e->getMessage(),
                ],
            ]);
            throw new ProductFileIsNotDownloadable();
        }

        $supplierCode = ($this->getSupplierCodeFromSupplierFileIdentifier)($query->productFileIdentifier);

        if (null === $supplierCode) {
            return null;
        }

        $this->eventDispatcher->dispatch(new ProductFileDownloaded(
            $query->productFileIdentifier,
            $supplierCode,
            $query->userId, // @todo Will be moved to the Infrastructure layer
        ));

        return new ProductFileNameAndResourceFile($productFilePathAndFileName->originalFilename, $productFileStream);
    }
}
