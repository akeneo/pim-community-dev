<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;

final class ManualStorageHydrator implements StorageHydratorInterface
{
    public function hydrate(array $normalizedStorage): StorageInterface
    {
        return new ManualStorage($normalizedStorage['file_path']);
    }

    public function supports(array $normalizedStorage): bool
    {
        return array_key_exists('type', $normalizedStorage) && $normalizedStorage['type'] === ManualStorage::TYPE;
    }
}
