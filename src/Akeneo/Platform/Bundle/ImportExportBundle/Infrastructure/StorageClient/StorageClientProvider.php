<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class StorageClientProvider
{
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
}
