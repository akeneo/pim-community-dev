<?php

namespace Akeneo\Channel\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedFindChannels implements FindChannels, CachedQueryInterface
{
    private ?array $cache = null;

    public function __construct(
        private FindChannels $findChannels
    ) {
    }

    public function findAll(): array
    {
        if (null === $this->cache) {
            $this->cache = $this->findChannels->findAll();
        }

        return $this->cache;
    }

    public function clearCache(): void
    {
        $this->cache = null;
    }
}
