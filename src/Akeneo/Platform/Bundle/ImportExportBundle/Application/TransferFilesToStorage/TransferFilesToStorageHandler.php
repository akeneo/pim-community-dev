<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\TransferFilesToStorageInterface;

final class TransferFilesToStorageHandler
{
    public function __construct(
        private StorageHydratorInterface $storageHydrator,
        private TransferFilesToStorageInterface $transferFilesToStorage
    ) {
    }

    public function handle(TransferFilesToStorageCommand $command)
    {
        $storage = $this->storageHydrator->hydrate($command->normalizedStorage);
        if (null === $storage) {
            return;
        }

        $this->transferFilesToStorage->transfer($command->filesToTransfer, $storage);
    }
}
