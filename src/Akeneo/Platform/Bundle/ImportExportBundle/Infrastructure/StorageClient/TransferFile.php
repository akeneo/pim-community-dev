<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;

final class TransferFile
{
    public function transfer(
        StorageClientInterface $sourceFilesystem,
        StorageClientInterface $destinationFilesystem,
        string $sourceFilePath,
        string $destinationFilePath
    ): void {
        if (!$sourceFilesystem->fileExists($sourceFilePath)) {
            throw new \RuntimeException(sprintf('The file "%s" is not present in the storage.', $sourceFilePath));
        }

        $stream = $sourceFilesystem->readStream($sourceFilePath);
        $destinationFilesystem->writeStream($destinationFilePath, $stream);
    }
}
