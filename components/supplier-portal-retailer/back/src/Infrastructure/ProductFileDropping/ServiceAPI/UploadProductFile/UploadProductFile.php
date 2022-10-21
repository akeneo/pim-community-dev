<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\InvalidProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CreateProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CreateProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ContributorDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\UnableToStoreProductFile;
use Psr\Log\LoggerInterface;

final class UploadProductFile
{
    public function __construct(
        private CreateProductFileHandler $createProductFileHandler,
        private LoggerInterface $logger,
    ) {
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
        } catch (InvalidProductFile $e) {
            throw new Exception\InvalidUploadedProductFile(message: $e->getMessage(), previous: $e);
        } catch (ContributorDoesNotExist $e) {
            $this->logger->error('The product file upload failed because the contributor does not exist.', [
                'data' => [
                    'contributor_email' => $uploadProductFileCommand->contributorEmail,
                ],
            ]);

            throw new Exception\InvalidUploadedProductFile(message: $e->getMessage(), previous: $e);
        } catch (UnableToStoreProductFile $e) {
            throw new Exception\UnableToStoreProductFile(message: $e->getMessage(), previous: $e);
        }
    }
}
