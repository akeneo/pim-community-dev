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

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\Local;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\SftpStorage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class LocalStorageClientProvider implements StorageClientProviderInterface
{
    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        if (!$storage instanceof LocalStorage) {
            throw new \InvalidArgumentException('The provider only support LocalStorage');
        }

        $dirname = dirname($storage->getFilePath());

        return new FileSystemStorageClient(new Filesystem(new LocalFilesystemAdapter($dirname)));
    }

    public function supports(StorageInterface $storage): bool
    {
        return $storage instanceof LocalStorage;
    }
}
