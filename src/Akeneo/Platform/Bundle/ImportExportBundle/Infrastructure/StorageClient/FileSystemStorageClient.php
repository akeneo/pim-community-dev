<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use League\Flysystem\FilesystemOperator;

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
