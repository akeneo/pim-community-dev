<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
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
        return array_key_exists('type', $normalizedStorage) && LocalStorage::TYPE === $normalizedStorage['type'];
    }
}
