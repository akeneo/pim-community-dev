<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\DownloadFileFromStorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;

final class DownloadFileFromStorageHandler
{
    public function __construct(
        private StorageHydratorInterface $storageHydrator,
        private DownloadFileFromStorageInterface $downloadFileFromStorage,
    ) {
    }

    public function handle(DownloadFileFromStorageCommand $command): string
    {
        $storage = $this->storageHydrator->hydrate($command->normalizedStorage);
        if (null === $storage) {
            throw new \InvalidArgumentException('Unable to download file from none storage');
        }

        return $this->downloadFileFromStorage->download($storage, $command->workingDirectory);
    }
}
