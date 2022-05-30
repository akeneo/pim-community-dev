<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\Manual;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\FileSystemStorageClient;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use League\Flysystem\FilesystemOperator;

final class ManualStorageClientProvider implements StorageClientProviderInterface
{
    public function __construct(
        private FilesystemOperator $jobFilesystemOperator,
    ) {
    }

    public function getFromStorage(StorageInterface $storage): StorageClientInterface
    {
        if (!$storage instanceof ManualStorage) {
            throw new \InvalidArgumentException('The provider only support ManualStorage');
        }

        return new FileSystemStorageClient($this->jobFilesystemOperator);
    }

    public function supports(StorageInterface $storage): bool
    {
        return $storage instanceof ManualStorage;
    }
}
