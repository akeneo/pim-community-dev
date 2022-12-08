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
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\AmazonS3Storage;

final class AmazonS3StorageHydrator implements StorageHydratorInterface
{
    public function hydrate(array $normalizedStorage): StorageInterface
    {
        return new AmazonS3Storage(
            $normalizedStorage['region'],
            $normalizedStorage['bucket'],
            $normalizedStorage['key'],
            $normalizedStorage['secret'],
            $normalizedStorage['file_path'],
        );
    }

    public function supports(array $normalizedStorage): bool
    {
        return array_key_exists('type', $normalizedStorage) && AmazonS3Storage::TYPE === $normalizedStorage['type'];
    }
}
