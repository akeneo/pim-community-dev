<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

final class StoreProductsFileInGCSBucket implements StoreProductsFile
{
    public function __construct(
        private FilesystemProvider $filesystemProvider,
        private string $databaseName, // @todo To replace by the tenant id (PIM instance name) once UCS implemented
    ) {
    }

    public function __invoke(string $supplierCode, Filename $filename, string $content): void
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        $fileSystem->createDirectory(sprintf('%s/%s', $this->databaseName, $supplierCode));
        $fileSystem->write(sprintf('%s/%s/%s', $this->databaseName, $supplierCode, $filename), $content);
    }
}
