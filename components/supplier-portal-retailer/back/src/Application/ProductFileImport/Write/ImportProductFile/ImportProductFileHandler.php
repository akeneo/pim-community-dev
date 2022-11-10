<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StreamStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;

final class ImportProductFileHandler
{
    public function __construct(
        private readonly ProductFileRepository $productFileRepository,
        private readonly StreamStoredProductFile $streamStoredProductFile,
        private readonly Write\LaunchProductFileImport $launchProductFileImport,
        private readonly ProductFileImportRepository $productFileImportRepository,
    ) {
    }

    public function __invoke(ImportProductFile $importProductFile): string
    {
        $productFile = $this->productFileRepository->find(Identifier::fromString($importProductFile->productFileIdentifier));
        if (null === $productFile) {
            throw new ProductFileDoesNotExist();
        }

        $productFileStream = ($this->streamStoredProductFile)($productFile->path());

        $importResult = ($this->launchProductFileImport)($importProductFile->importProductFileConfigurationCode, $productFile->originalFilename(), $productFileStream);

        $productFileImport = ProductFileImport::start($productFile, $importResult->importExecutionId);
        $this->productFileImportRepository->save($productFileImport);

        return $importResult->importExecutionUrl;
    }
}
