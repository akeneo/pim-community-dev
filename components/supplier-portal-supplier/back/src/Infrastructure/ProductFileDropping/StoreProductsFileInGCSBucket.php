<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

final class StoreProductsFileInGCSBucket implements StoreProductsFile
{
    public function __construct(private FilesystemProvider $filesystemProvider)
    {
    }

    public function __invoke(Code $supplierCode, Filename $filename, string $content): void
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        $fileSystem->createDirectory((string) $supplierCode);
        $fileSystem->write(sprintf('%s/%s', $supplierCode, $filename), $content);
    }
}
