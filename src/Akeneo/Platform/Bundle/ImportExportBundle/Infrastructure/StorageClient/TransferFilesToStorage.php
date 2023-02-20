<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Event\FileCannotBeExported;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\TransferFilesToStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class TransferFilesToStorage implements TransferFilesToStorageInterface
{
    public function __construct(
        private StorageClientProvider $storageClientProvider,
        private TransferFile $transferFile,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param FileToTransfer[] $filesToTransfer
     */
    public function transfer(array $filesToTransfer, StorageInterface $storage): void
    {
        $destinationStorage = $this->storageClientProvider->getFromStorage($storage);
        foreach ($filesToTransfer as $fileToTransfer) {
            if ($storage instanceof LocalStorage && $fileToTransfer->isLocal()) {
                continue;
            }

            $sourceStorage = $this->storageClientProvider->getFromFileToTransfer($fileToTransfer);

            try {
                $this->transferFile->transfer(
                    $sourceStorage,
                    $destinationStorage,
                    $fileToTransfer->getFileKey(),
                    $fileToTransfer->getOutputFileName()
                );
            } catch (\Exception $exception) {
                $this->eventDispatcher->dispatch(new FileCannotBeExported($exception->getMessage()));
            }
        }
    }
}
