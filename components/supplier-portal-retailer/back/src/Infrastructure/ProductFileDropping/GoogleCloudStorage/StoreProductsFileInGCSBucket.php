<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

final class StoreProductsFileInGCSBucket implements StoreProductsFile
{
    public function __construct(private FilesystemProvider $filesystemProvider)
    {
    }

    public function __invoke(
        Code $supplierCode,
        Filename $originalFilename,
        Identifier $identifier,
        string $temporaryPath,
    ): string {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        $fileSystem->createDirectory((string) $supplierCode);
        $path = sprintf('%s/%s-%s', $supplierCode, $identifier, $originalFilename);

        if (!is_readable($temporaryPath)) {
            throw new \RuntimeException();
        }

        $contents = fopen($temporaryPath, 'r');
        $fileSystem->writeStream($path, $contents);

        if (is_resource($contents)) {
            fclose($contents);
        }

        return $path;
    }
}
