<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
