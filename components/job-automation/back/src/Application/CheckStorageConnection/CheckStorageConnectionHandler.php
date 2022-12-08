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

namespace Akeneo\Platform\JobAutomation\Application\CheckStorageConnection;

use Akeneo\Platform\JobAutomation\Infrastructure\StorageClient\RemoteStorageClientInterface;

final class CheckStorageConnectionHandler
{
    public function __construct(
        private readonly iterable $storageClientProviders,
    ) {
    }

    public function handle(CheckStorageConnectionQuery $storageConnectionCheckQuery): bool
    {
        foreach ($this->storageClientProviders as $storageClientProvider) {
            if ($storageClientProvider->supports($storageConnectionCheckQuery->getStorage())) {
                $storageClient = $storageClientProvider->getFromStorage($storageConnectionCheckQuery->getStorage());

                if (!$storageClient instanceof RemoteStorageClientInterface) {
                    throw new \LogicException();
                }

                return $storageClient->isConnectionValid();
            }
        }

        return false;
    }
}
