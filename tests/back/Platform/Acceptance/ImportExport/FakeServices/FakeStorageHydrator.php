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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;

final class FakeStorageHydrator implements StorageHydratorInterface
{
    public function hydrate(array $normalizedStorage): StorageInterface|NoneStorage
    {
        return new FakeStorage();
    }

    public function supports(array $normalizedStorage): bool
    {
        return array_key_exists('type', $normalizedStorage) && "fake" === $normalizedStorage['type'];
    }
}