<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StreamStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write;

final class ImportProductFileHandler
{
    public function __construct(
        private readonly GetProductFilePathAndFileName $getProductFilePathAndFileName,
        private readonly StreamStoredProductFile $streamStoredProductFile,
        private readonly Write\LaunchProductFileImport $launchProductFileImport,
        private readonly ProductFileImportRepository $productFileImportRepository,
    ) {
    }

    public function __invoke(ImportProductFile $importProductFile): string
    {
        $productFilePathAndFileName = ($this->getProductFilePathAndFileName)($importProductFile->productFileIdentifier);
        if (null === $productFilePathAndFileName) {
            throw new ProductFileDoesNotExist();
        }

        $productFileStream = ($this->streamStoredProductFile)($productFilePathAndFileName->path);

        $importResult = ($this->launchProductFileImport)($importProductFile->code, $productFilePathAndFileName->originalFilename, $productFileStream);

        $productFileImport = ProductFileImport::start($importProductFile->productFileIdentifier, $importResult->importExecutionId);
        $this->productFileImportRepository->save($productFileImport);

        return $importResult->importExecutionUrl;
    }
}
