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

final class StorageHydrator implements StorageHydratorInterface
{
    public function __construct(private iterable $storageHydrators)
    {
    }

    public function hydrate(array $normalizedStorage): StorageInterface|NoneStorage
    {
        foreach ($this->storageHydrators as $storageHydrator) {
            if ($storageHydrator->supports($normalizedStorage)) {
                return $storageHydrator->hydrate($normalizedStorage);
            }
        }

        throw new \LogicException(sprintf('No storage hydrator found for the given storage: %s', json_encode($normalizedStorage)));
    }

    public function supports(array $normalizedStorage): bool
    {
        foreach ($this->storageHydrators as $storageHydrator) {
            if ($storageHydrator->supports($normalizedStorage)) {
                return true;
            }
        }

        return false;
    }
}
