<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

final class DownloadStoredProductFileFromGCSBucket implements DownloadStoredProductFile
{
    public function __construct(private FilesystemProvider $filesystemProvider)
    {
    }

    //@phpstan-ignore-next-line
    public function __invoke(string $path)
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        if (!$fileSystem->fileExists($path)) {
            throw new \RuntimeException('The requested file does not exist on the bucket.');
        }

        return $fileSystem->readStream($path);
    }
}
