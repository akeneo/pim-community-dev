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

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class FileSystemStorageClient implements StorageClientInterface
{
    public function __construct(private FilesystemOperator $filesystemOperator)
    {
    }

    public function fileExists(string $filePath): bool
    {
        return $this->filesystemOperator->fileExists($filePath);
    }

    /**
     * @return resource
     */
    public function readStream(string $filePath)
    {
        return $this->filesystemOperator->readStream($filePath);
    }

    /**
     * @param resource $content
     */
    public function writeStream(string $filePath, $content): void
    {
        $this->filesystemOperator->writeStream($filePath, $content);
    }
}
