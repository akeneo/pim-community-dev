<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\DownloadFileFromStorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Event\FileCannotBeImported;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class DownloadFileFromStorage implements DownloadFileFromStorageInterface
{
    private const MAX_FILE_SIZE = 512_000_000;

    public function __construct(
        private StorageClientProvider $storageClientProvider,
        private TransferFile $transferFile,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function download(StorageInterface $sourceStorage, string $workingDirectory): string
    {
        $sourceStorageClient = $this->storageClientProvider->getFromStorage($sourceStorage);
        $destinationStorageClient = $this->storageClientProvider->getLocalStorageClient();

        $sourceFilePath = $sourceStorage->getFilePath();
        $destinationFilePath = $workingDirectory.basename($sourceFilePath);

        try {
            $this->validateFileBeforeDownload($sourceStorage, $sourceStorageClient, $sourceFilePath);

            $this->transferFile->transfer(
                $sourceStorageClient,
                $destinationStorageClient,
                $sourceFilePath,
                $destinationFilePath,
            );
        } catch (\Exception $exception) {
            $message = $exception->getPrevious() ? $exception->getPrevious()->getMessage() : $exception->getMessage();
            $this->eventDispatcher->dispatch(new FileCannotBeImported($message));
        }

        return $destinationFilePath;
    }

    private function validateFileBeforeDownload(
        StorageInterface $sourceStorage,
        StorageClientInterface $storageClient,
        string $filePath,
    ): void {
        try {
            $fileExists = $storageClient->fileExists($filePath);
        } catch (\Exception $exception) {
            if (!$sourceStorage instanceof LocalStorage) {
                throw new \RuntimeException('Cannot connect to the server, please check your connection settings and try again.');
            }

            throw $exception;
        }

        if (!$fileExists) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist in the selected storage.', $filePath));
        }

        $fileSize = $storageClient->getFileSize($filePath);

        if (self::MAX_FILE_SIZE < $fileSize) {
            throw new \RuntimeException('The file is too large to be downloaded.');
        }
    }
}
