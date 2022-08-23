<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\DeleteProductFilesFromPaths;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToDeleteDirectory;
use Psr\Log\LoggerInterface;

final class DeleteProductFilesFromPathsInGCSBucket implements DeleteProductFilesFromPaths
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
            } catch (UnableToDeleteDirectory | FilesystemException $e) {
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
