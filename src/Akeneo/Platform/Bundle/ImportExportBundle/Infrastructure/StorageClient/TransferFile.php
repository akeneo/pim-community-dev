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

final class TransferFile
{
    public function transfer(
        StorageClientInterface $sourceFilesystem,
        StorageClientInterface $destinationFilesystem,
        string $sourceFilePath,
        string $destinationFilePath
    ): void {
        if (!$sourceFilesystem->fileExists($sourceFilePath)) {
            throw new \RuntimeException(sprintf('The file "%s" is not present on the source filesystem.', $sourceFilePath));
        }

        $stream = $sourceFilesystem->readStream($sourceFilePath);
        $destinationFilesystem->writeStream($destinationFilePath, $stream);
    }
}
