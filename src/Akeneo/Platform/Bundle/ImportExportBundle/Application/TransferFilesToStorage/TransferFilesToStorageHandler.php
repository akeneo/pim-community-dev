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
        if ($storage instanceof NoneStorage) {
            return;
        }

        $this->transferFilesToStorage->transfer($command->filesToTransfer, $storage);
    }
}
