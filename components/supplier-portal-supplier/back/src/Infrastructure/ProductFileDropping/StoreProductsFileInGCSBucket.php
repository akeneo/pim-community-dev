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
        private string $pimTenantName, // @todo To replace by the PIM tenant name once UCS implemented
    ) {
    }

    public function __invoke(string $supplierCode, Filename $filename, string $content): void
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        $fileSystem->createDirectory(sprintf('%s/%s', $this->pimTenantName, $supplierCode));
        $fileSystem->write(sprintf('%s/%s/%s', $this->pimTenantName, $supplierCode, $filename), $content);
    }
}
