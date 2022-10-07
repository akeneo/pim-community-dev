<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\UnableToStoreProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;

final class StoreProductsFileInGCSBucket implements StoreProductsFile
{
    public function __construct(private FilesystemProvider $filesystemProvider, private LoggerInterface $logger)
    {
    }

    public function __invoke(
        Code $supplierCode,
        Filename $originalFilename,
        Identifier $identifier,
        string $temporaryPath,
    ): string {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        $path = sprintf('%s/%s-%s', $supplierCode, $identifier, $originalFilename);

        try {
            $fileSystem->createDirectory((string) $supplierCode);

            if (!is_readable($temporaryPath)) {
                throw new UnableToStoreProductFile();
            }

            $contents = fopen($temporaryPath, 'r');
            $fileSystem->writeStream($path, $contents);

            if (is_resource($contents)) {
                fclose($contents);
            }
        } catch (FilesystemException $e) {
            $this->logger->error('Product file could not be stored.', [
                'data' => [
                    'fileIdentifier' => (string) $identifier,
                    'filename' => (string) $originalFilename,
                    'path' => $path,
                    'error' => $e->getMessage(),
                ],
            ]);
            throw new UnableToStoreProductFile();
        }

        return $path;
    }
}
