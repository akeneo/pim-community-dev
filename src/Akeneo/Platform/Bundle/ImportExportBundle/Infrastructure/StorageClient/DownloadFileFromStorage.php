<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\DownloadFileFromStorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Event\FileCannotBeImported;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class DownloadFileFromStorage implements DownloadFileFromStorageInterface
{
    private const MAX_FILE_SIZE = 512_000_000;

    public function __construct(
        private StorageClientProvider $storageClientProvider,
        private TransferFile $transferFile,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function download(StorageInterface $sourceStorage, string $workingDirectory): string
    {
        $sourceStorageClient = $this->storageClientProvider->getFromStorage($sourceStorage);
        $destinationStorageClient = $this->storageClientProvider->getLocalStorageClient();

        $sourceFilePath = $sourceStorage->getFilePath();
        $destinationFilePath = $workingDirectory.basename($sourceFilePath);

        try {
            $this->validateFileBeforeDownload($sourceStorageClient, $sourceFilePath);

            $this->transferFile->transfer(
                $sourceStorageClient,
                $destinationStorageClient,
                $sourceFilePath,
                $destinationFilePath
            );
        } catch (\Exception $exception) {
            $message = $exception->getPrevious() ? $exception->getPrevious()->getMessage() : $exception->getMessage();
            $this->eventDispatcher->dispatch(new FileCannotBeImported($message));
        }

        return $destinationFilePath;
    }

    private function validateFileBeforeDownload(StorageClientInterface $storageClient, string $filePath): void
    {
        $fileExists = $storageClient->fileExists($filePath);

        if (!$fileExists) {
            throw new \RuntimeException(sprintf('The file "%s" is not present in the storage.', $filePath));
        }

        $fileSize = $storageClient->getFileSize($filePath);

        if (self::MAX_FILE_SIZE < $fileSize) {
            throw new \RuntimeException('The file is too large to be downloaded');
        }
    }
}
