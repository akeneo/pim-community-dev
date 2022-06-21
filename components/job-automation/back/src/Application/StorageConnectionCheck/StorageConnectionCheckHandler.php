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

namespace Akeneo\Platform\JobAutomation\Application\StorageConnectionCheck;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProvider;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;

final class StorageConnectionCheckHandler
{
    public function __construct(
        private iterable $storageClientProviders,
    ) {
    }

    public function handle(StorageConnectionCheckQuery $storageConnectionCheckQuery): void
    {
        foreach ($this->storageClientProviders as $storageClientProvider) {
            if ($storageClientProvider->supports($storageConnectionCheckQuery->getStorage())) {
                $connection = $storageClientProvider->getConnectionProvider($storageConnectionCheckQuery->getStorage());
                $connection->provideConnection();
            }
        }
    }
}
