<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StreamStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;

final class StreamStoredProductFileFromGCSBucket implements StreamStoredProductFile
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
                $this->logger->error('Product file does not exist.', ['data' => ['path' => $path,],]);
                throw new ProductFileDoesNotExist('The requested file does not exist on the bucket.');
            }

            return $fileSystem->readStream($path);
        } catch (FilesystemException $e) {
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
