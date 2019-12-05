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

use League\Flysystem\FilesystemNotFoundException;
use League\Flysystem\MountManager;
use League\Flysystem\RootViolationException;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileStorageChecker
{
    private const STORAGE_PREFIXES = [
        'catalogStorage',
        'archivist',
        'jobsStorage',
        'assetManagerStorage',
        'tmpAssetUpload',
        'tmpStorage'
    ];

    /** @var MountManager */
    private $mountManager;

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

        return empty($failingFileStorages) ?
            ServiceStatus::ok() :
            ServiceStatus::notOk('Failing file storages: ' . implode(',', $failingFileStorages));
    }

    private function isFileStorageAvailable(string $prefix): bool
    {
        try {
            $filesystem = $this->mountManager->getFilesystem($prefix);
            $dirname = Uuid::uuid4();
            $isCreationOk = $filesystem->createDir($dirname);
            $isDeletionOk = $filesystem->deleteDir($dirname);

            return $isCreationOk && $isDeletionOk;
        } catch (FilesystemNotFoundException|RootViolationException|UnsatisfiedDependencyException|\InvalidArgumentException $e) {
            return false;
        }
    }
}
