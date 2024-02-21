<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;

final class TransferFile
{
    private const TEMPORARY_DESTINATION_FILE_PATH_PREFIX = '.tmp-';

    public function transfer(
        StorageClientInterface $sourceFilesystem,
        StorageClientInterface $destinationFilesystem,
        string $sourceFilePath,
        string $destinationFilePath
    ): void {
        if (!$sourceFilesystem->fileExists($sourceFilePath)) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist in the selected storage.', $sourceFilePath));
        }

        try {
            $stream = $sourceFilesystem->readStream($sourceFilePath);
        } catch (\Exception) {
            throw new \RuntimeException('File is not readable.');
        }

        $this->writeOnDestinationFilesystem($destinationFilesystem, $destinationFilePath, $stream);
    }

    /**
     * @param resource $stream
     */
    private function writeOnDestinationFilesystem(StorageClientInterface $destinationFilesystem, string $destinationFilePath, $stream): void
    {
        $temporaryDestinationFilePath = $this->getTemporaryDestinationFilePath($destinationFilePath);
        $destinationFilesystem->writeStream($temporaryDestinationFilePath, $stream);

        if ($destinationFilesystem->fileExists($destinationFilePath)) {
            $destinationFilesystem->delete($destinationFilePath);
        }

        $destinationFilesystem->move($temporaryDestinationFilePath, $destinationFilePath);
    }

    private function getTemporaryDestinationFilePath(string $destinationFilePath): string
    {
        $destinationFilePathInfo = pathinfo($destinationFilePath);

        $dirname = '.' !== $destinationFilePathInfo['dirname'] ? $destinationFilePathInfo['dirname'] : '';
        $separator = '' !== $dirname ? '/' : '';
        $basename = sprintf('%s%s', self::TEMPORARY_DESTINATION_FILE_PATH_PREFIX, $destinationFilePathInfo['basename']);

        return sprintf(
            '%s%s%s',
            $dirname,
            $separator,
            $basename,
        );
    }
}
