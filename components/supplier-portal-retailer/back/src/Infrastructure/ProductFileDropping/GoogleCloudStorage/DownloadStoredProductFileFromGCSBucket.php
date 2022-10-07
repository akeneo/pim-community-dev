<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToReadFile;
use Psr\Log\LoggerInterface;

final class DownloadStoredProductFileFromGCSBucket implements DownloadStoredProductFile
{
    public function __construct(private FilesystemProvider $filesystemProvider, private LoggerInterface $logger)
    {
    }

    //@phpstan-ignore-next-line
    public function __invoke(string $path)
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        try {
            if (!$fileSystem->fileExists($path)) {
                throw new ProductFileDoesNotExist('The requested file does not exist on the bucket.');
            }

            return $fileSystem->readStream($path);
        } catch (FilesystemException | UnableToCheckFileExistence | UnableToReadFile $e) {
            $this->logger->error('Product file could not be downloaded.', [
                'data' => [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ],
            ]);

            throw new UnableToReadProductFile(previous: $e);
        }
    }
}
