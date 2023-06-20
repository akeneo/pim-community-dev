<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\Local;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
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

        return new FileSystemStorageClient(new Filesystem(new LocalFilesystemAdapter(
            location: $dirname,
            lazyRootCreation: true,
        )));
    }

    public function supports(StorageInterface $storage): bool
    {
        return $storage instanceof LocalStorage;
    }
}
