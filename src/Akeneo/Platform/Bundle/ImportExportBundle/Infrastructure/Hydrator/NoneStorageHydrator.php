<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;

final class NoneStorageHydrator implements StorageHydratorInterface
{
    public function hydrate(array $normalizedStorage): StorageInterface|NoneStorage
    {
        return new NoneStorage();
    }

    public function supports(array $normalizedStorage): bool
    {
        return array_key_exists('type', $normalizedStorage) && NoneStorage::TYPE === $normalizedStorage['type'];
    }
}
