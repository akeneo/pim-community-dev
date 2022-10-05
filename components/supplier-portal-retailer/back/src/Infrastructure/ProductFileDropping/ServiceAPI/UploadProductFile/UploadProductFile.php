<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\InvalidProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\UnableToStoreProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CreateProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CreateProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\ContributorDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\Exception;

final class UploadProductFile
{
    public function __construct(private CreateProductFileHandler $createProductFileHandler)
    {
    }

    public function __invoke(UploadProductFileCommand $uploadProductFileCommand): void
    {
        try {
            ($this->createProductFileHandler)(
                new CreateProductFile(
                    $uploadProductFileCommand->uploadedFile->getClientOriginalName(),
                    $uploadProductFileCommand->uploadedFile->getPathname(),
                    $uploadProductFileCommand->contributorEmail,
                ),
            );
        } catch (InvalidProductFile | ContributorDoesNotExist $e) {
            throw new Exception\InvalidUploadedProductFile(message: $e->getMessage(), previous: $e);
        } catch (UnableToStoreProductFile $e) {
            throw new Exception\UnableToStoreProductFile(message: $e->getMessage(), previous: $e);
        }
    }
}
