<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileIsNotDownloadable;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetSupplierFilePath;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DownloadProductFileHandler
{
    public function __construct(
        private GetSupplierFilePath $getSupplierFilePath,
        private DownloadStoredProductFile $downloadStoredProductsFile,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    //@phpstan-ignore-next-line
    public function __invoke(DownloadProductFile $query)
    {
        $supplierFilePath = ($this->getSupplierFilePath)($query->supplierFileIdentifier);
        if (null === $supplierFilePath) {
            throw new SupplierFileDoesNotExist();
        }

        try {
            //FlySystem can throw exceptions that do not extend \Exception but \Throwable, wich is one level higher
            $supplierFileStream = ($this->downloadStoredProductsFile)($supplierFilePath);
        } catch (\Throwable $e) {
            $this->logger->error('Supplier file could not be downloaded', [
                'data' => [
                    'fileIdentifier' => $query->supplierFileIdentifier,
                    'path' => $supplierFilePath,
                    'error' => $e->getMessage(),
                ],
            ]);
            throw new SupplierFileIsNotDownloadable();
        }

        $this->eventDispatcher->dispatch(new ProductFileDownloaded($query->supplierFileIdentifier));

        return $supplierFileStream;
    }
}
