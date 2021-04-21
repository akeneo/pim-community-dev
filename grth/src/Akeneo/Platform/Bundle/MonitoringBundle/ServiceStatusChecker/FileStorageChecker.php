<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use Ramsey\Uuid\Uuid;

final class FileStorageChecker
{
    private const STORAGE_PREFIXES = [
        'catalogStorage',
        'archivist',
        'jobsStorage',
        'assetStorage',
    ];

    private FileSystemProvider $filesystemProvider;

    public function __construct(FilesystemProvider $filesystemProvider)
    {
        $this->filesystemProvider = $filesystemProvider;
    }

    public function status(): ServiceStatus
    {
        $failingFileStorages = [];
        foreach (self::STORAGE_PREFIXES as $prefix) {
            if (!$this->isFileStorageAvailable($prefix)) {
                $failingFileStorages[] = $prefix;
            }
        }

        if (!$this->isTemporaryStorageAvailable()) {
            $failingFileStorages[] = 'tmpStorage';
        }

        return empty($failingFileStorages) ?
            ServiceStatus::ok() :
            ServiceStatus::notOk('Failing file storages: ' . implode(',', $failingFileStorages));
    }

    private function isFileStorageAvailable(string $prefix): bool
    {
        try {
            $filesystem = $this->filesystemProvider->getFilesystem($prefix);
            $filename = Uuid::uuid4()->toString();

            $filesystem->write($filename, 'monitoring');
            $content = $filesystem->read($filename);
            $filesystem->delete($filename);

            return 'monitoring' === $content;
        } catch (FilesystemException $e) {
            return false;
        }
    }

    /**
     * Temporary file storage does not use Flysystem. We test with basic method.
     */
    private function isTemporaryStorageAvailable(): bool
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . Uuid::uuid4();
        $isCreationOk = file_put_contents($path, 'monitoring');
        $contentInFile = file_get_contents($path);
        $isDeletionOk = unlink($path);

        return false !== $isCreationOk && 'monitoring' === $contentInFile && true === $isDeletionOk;
    }
}
