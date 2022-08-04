<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

final class DownloadStoredProductFileInGCSBucket implements DownloadStoredProductFile
{
    public function __construct(private FilesystemProvider $filesystemProvider)
    {
    }

    //@phpstan-ignore-next-line
    public function __invoke(Path $path)
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        if (!$fileSystem->fileExists((string) $path)) {
            throw new \RuntimeException('The requested file does not exist on the bucket.');
        }

        return $fileSystem->readStream((string) $path);
    }
}
