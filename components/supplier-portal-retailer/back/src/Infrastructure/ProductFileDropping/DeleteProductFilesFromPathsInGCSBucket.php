<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\DeleteProductFilesFromPaths;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

final class DeleteProductFilesFromPathsInGCSBucket implements DeleteProductFilesFromPaths
{
    public function __construct(private FilesystemProvider $filesystemProvider)
    {
    }

    public function __invoke(array $productFilePaths): void
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        foreach ($productFilePaths as $productFilePath) {
            $fileSystem->delete($productFilePath);
        }
    }
}
