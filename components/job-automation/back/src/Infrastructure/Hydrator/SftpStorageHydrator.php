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
use Akeneo\Platform\JobAutomation\Domain\Query\GetAsymmetricKeysQueryInterface;

final class SftpStorageHydrator implements StorageHydratorInterface
{
    public function __construct(
        private GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery,
    ) {
    }

    public function hydrate(array $normalizedStorage): StorageInterface
    {
        return match ($normalizedStorage['login_type']) {
            SftpStorage::LOGIN_TYPE_PASSWORD => $this->hydrateForPasswordLoginType($normalizedStorage),
            SftpStorage::LOGIN_TYPE_PRIVATE_KEY => $this->hydrateForPrivateKeyLoginType($normalizedStorage),
            default => throw new \LogicException(sprintf('Unsupported login type "%s"', $normalizedStorage['login_type'])),
        };
    }

    private function hydrateForPasswordLoginType(array $normalizedStorage): StorageInterface
    {
        return new SftpStorage(
            $normalizedStorage['host'],
            $normalizedStorage['port'],
            $normalizedStorage['login_type'],
            $normalizedStorage['username'],
            $normalizedStorage['password'],
            $normalizedStorage['file_path'],
            null,
            null,
            $normalizedStorage['fingerprint'] ?? null,
        );
    }

    private function hydrateForPrivateKeyLoginType(array $normalizedStorage): StorageInterface
    {
        $asymmetricKeys = $this->getAsymmetricKeysQuery->execute();

        return new SftpStorage(
            $normalizedStorage['host'],
            $normalizedStorage['port'],
            $normalizedStorage['login_type'],
            $normalizedStorage['username'],
            null,
            $normalizedStorage['file_path'],
            $asymmetricKeys->getPrivateKey(),
            $asymmetricKeys->getPublicKey(),
            $normalizedStorage['fingerprint'] ?? null,
        );
    }

    public function supports(array $normalizedStorage): bool
    {
        return array_key_exists('type', $normalizedStorage) && SftpStorage::TYPE === $normalizedStorage['type'];
    }
}
