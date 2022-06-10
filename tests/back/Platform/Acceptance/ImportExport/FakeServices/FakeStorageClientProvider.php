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

namespace AkeneoTest\Platform\Acceptance\ImportExport\FakeServices;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use League\Flysystem\Filesystem;

class FakeStorageClientProvider implements StorageClientProviderInterface
{
    public function __construct(
        private Filesystem $fileSystem,
        private string $storageClassName,
    ) {
    }

    public function supports(StorageInterface $storage): bool
    {
        return $storage instanceof $this->storageClassName;
    }

    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        return new FileSystemStorageClient($this->fileSystem);
    }
}
