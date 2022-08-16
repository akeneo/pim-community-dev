<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileIsNotDownloadable;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetCodeFromSupplierFileIdentifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DownloadProductFileHandler
{
    public function __construct(
        private GetProductFilePathAndFileName $getProductFilePathAndFileName,
        private DownloadStoredProductFile $downloadStoredProductFile,
        private EventDispatcherInterface $eventDispatcher,
        private GetCodeFromSupplierFileIdentifier $getCodeFromSupplierFileIdentifier,
        private LoggerInterface $logger,
    ) {
    }

    //@phpstan-ignore-next-line
    public function __invoke(DownloadProductFile $query)
    {
        $productFilePathAndFileName = ($this->getProductFilePathAndFileName)($query->supplierFileIdentifier);
        if (null === $productFilePathAndFileName) {
            throw new SupplierFileDoesNotExist();
        }

        try {
            // FlySystem can throw exceptions that do not extend \Exception but \Throwable, which is one level higher
            $productFileStream = ($this->downloadStoredProductFile)($productFilePathAndFileName->path);
        } catch (\Throwable $e) {
            $this->logger->error('Supplier file could not be downloaded', [
                'data' => [
                    'fileIdentifier' => $query->supplierFileIdentifier,
                    'filename' => $productFilePathAndFileName->originalFilename,
                    'path' => $productFilePathAndFileName->path,
                    'error' => $e->getMessage(),
                ],
            ]);
            throw new SupplierFileIsNotDownloadable();
        }

        $supplierCode = ($this->getCodeFromSupplierFileIdentifier)($query->supplierFileIdentifier);

        if (null === $supplierCode) {
            return null;
        }

        $this->eventDispatcher->dispatch(new ProductFileDownloaded(
            $query->supplierFileIdentifier,
            $supplierCode,
            $query->userId,
        ));

        return new ProductFileNameAndResourceFile($productFilePathAndFileName->originalFilename, $productFileStream);
    }
}
