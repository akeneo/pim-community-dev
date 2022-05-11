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

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;

final class StorageHydrator implements StorageHydratorInterface
{
    public function __construct(private iterable $storageHydrators)
    {
    }

    public function hydrate(array $normalizedStorage): StorageInterface
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
