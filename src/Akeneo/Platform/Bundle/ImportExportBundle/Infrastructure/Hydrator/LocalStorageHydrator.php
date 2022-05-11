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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;
use Akeneo\Platform\VersionProviderInterface;
use Webmozart\Assert\Assert;

final class LocalStorageHydrator implements StorageHydratorInterface
{
    public function __construct(private VersionProviderInterface $versionProvider)
    {
    }

    public function hydrate(array $normalizedStorage): StorageInterface
    {
        Assert::false($this->versionProvider->isSaaSVersion(), 'Local storage is not available in SaaS version');

        return new LocalStorage($normalizedStorage['file_path']);
    }

    public function supports(array $normalizedStorage): bool
    {
        return array_key_exists('type', $normalizedStorage) && $normalizedStorage['type'] === LocalStorage::TYPE;
    }
}
