<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\GoogleCloudStorage;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\GetAllSupplierCodes;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToDeleteFile;
use Psr\Log\LoggerInterface;

class DeleteUnknownSupplierDirectoriesInGCSBucket
{
    public function __construct(
        private FilesystemProvider $filesystemProvider,
        private GetAllSupplierCodes $getAllSupplierCodes,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(): void
    {
        $supplierCodes = ($this->getAllSupplierCodes)();
        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        foreach ($fileSystem->listContents('./') as $supplierDirectory) {
            if (!in_array($supplierDirectory->path(), $supplierCodes)) {
                try {
                    $fileSystem->delete($supplierDirectory->path());
                } catch (UnableToDeleteFile|FilesystemException $e) {
                    $this->logger->error('Supplier directory could not be deleted.', [
                        'data' => [
                            'path' => $supplierDirectory->path(),
                            'error' => $e->getMessage(),
                        ],
                    ]);
                }
            }
        }
    }
}
