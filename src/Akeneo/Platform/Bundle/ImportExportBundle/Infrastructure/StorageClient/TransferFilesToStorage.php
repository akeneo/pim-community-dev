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
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\TransferFilesToStorageInterface;

final class TransferFilesToStorage implements TransferFilesToStorageInterface
{
    public function __construct(
        private StorageClientProvider $storageClientProvider,
        private TransferFile $transferFile
    ) {
    }

    /**
     * @param FileToTransfer[] $filesToTransfer
     */
    public function transfer(array $filesToTransfer, StorageInterface $storage): void
    {
        $destinationStorage = $this->storageClientProvider->getFromStorage($storage);
        foreach ($filesToTransfer as $fileToTransfer) {
            $sourceStorage = $this->storageClientProvider->getFromFileToTransfer($fileToTransfer);

            $this->transferFile->transfer(
                $sourceStorage,
                $destinationStorage,
                $fileToTransfer->getFileKey(),
                $fileToTransfer->getOutputFilePath()
            );
        }
    }
}
