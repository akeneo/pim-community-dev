<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DeleteProductFilesFromPaths;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToDeleteFile;
use Psr\Log\LoggerInterface;

class DeleteProductFilesFromPathsInGCSBucket implements DeleteProductFilesFromPaths
{
    public function __construct(private FilesystemProvider $filesystemProvider, private LoggerInterface $logger)
    {
    }

    public function __invoke(array $productFilePaths): void
    {
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        foreach ($productFilePaths as $productFilePath) {
            try {
                $fileSystem->delete($productFilePath);
            } catch (UnableToDeleteFile | FilesystemException $e) {
                $this->logger->error('Product file could not be deleted.', [
                    'data' => [
                        'path' => $productFilePath,
                        'error' => $e->getMessage(),
                    ],
                ]);
            }
        }
    }
}
