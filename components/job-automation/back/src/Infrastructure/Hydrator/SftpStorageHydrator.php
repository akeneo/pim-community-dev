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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\SftpStorage;

final class SftpStorageHydrator implements StorageHydratorInterface
{
    public function hydrate(array $normalizedStorage): StorageInterface
    {
        return new SftpStorage(
            $normalizedStorage['host'],
            $normalizedStorage['port'],
            $normalizedStorage['login_type'],
            $normalizedStorage['username'],
            $normalizedStorage['password'],
            $normalizedStorage['file_path'],
            $normalizedStorage['fingerprint'] ?? null,
        );
    }

    public function supports(array $normalizedStorage): bool
    {
        return array_key_exists('type', $normalizedStorage) && SftpStorage::TYPE === $normalizedStorage['type'];
    }

    private function getConnectionKey()
    {

    }
}
