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

use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\MountManager;
use Ramsey\Uuid\Uuid;

final class FileStorageChecker
{
    private const STORAGE_PREFIXES = [
        'catalogStorage',
        'archivist',
        'jobsStorage',
        'assetStorage'
    ];

    private MountManager $mountManager;

    public function __construct(MountManager $mountManager)
    {
        $this->mountManager = $mountManager;
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
            $filesystem = $this->mountManager->getFilesystem($prefix);
            $filename = Uuid::uuid4();
            $isCreationOk = $filesystem->write($filename, 'monitoring');
            $isDeletionOk = $filesystem->readAndDelete($filename);

            return $isCreationOk && $isDeletionOk === 'monitoring';
        } catch (FileNotFoundException|FileExistsException $e) {
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
