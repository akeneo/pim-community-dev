<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

final class StorageClientProvider
{
    private const LOCAL_FILESYSTEM_NAME = 'localFilesystem';

    /**
     * @param iterable<StorageClientProviderInterface> $storageClientProviders
     */
    public function __construct(
        private FilesystemProvider $filesystemProvider,
        private iterable $storageClientProviders,
    ) {
    }

    public function getFromFileToTransfer(FileToTransfer $fileToTransfer): StorageClientInterface
    {
        return new FileSystemStorageClient($this->filesystemProvider->getFilesystem($fileToTransfer->getStorage()));
    }

    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        foreach ($this->storageClientProviders as $storageClientProvider) {
            if ($storageClientProvider->supports($storage)) {
                return $storageClientProvider->getFromStorage($storage);
            }
        }

        throw new \RuntimeException(sprintf('No storage client found for storage "%s"', get_class($storage)));
    }

    public function getLocalStorageClient(): StorageClientInterface
    {
        return new FileSystemStorageClient($this->filesystemProvider->getFilesystem(self::LOCAL_FILESYSTEM_NAME));
    }
}
