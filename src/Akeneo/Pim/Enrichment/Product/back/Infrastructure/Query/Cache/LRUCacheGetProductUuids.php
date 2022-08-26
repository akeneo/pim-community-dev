<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Cache;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetProductUuids;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LRUCacheGetProductUuids implements GetProductUuids, CachedQueryInterface
{
    private LRUCache $cache;

    public function __construct(private GetProductUuids $getProductUuids)
    {
        $this->cache = new LRUCache(1000);
    }

    public function fromIdentifier(string $identifier): ?UuidInterface
    {
        $fetchNonFoundProductUuid = function (string $identifier): ?UuidInterface {
            return $this->getProductUuids->fromIdentifier($identifier);
        };

        return $this->cache->getForKey($identifier, $fetchNonFoundProductUuid);
    }

    public function fromIdentifiers(array $identifiers): array
    {
        $fetchNonFoundIdentifiers = function (array $identifiersNotFound): array {
            return $this->getProductUuids->fromIdentifiers($identifiersNotFound);
        };

        return $this->cache->getForKeys($identifiers, $fetchNonFoundIdentifiers);
    }

    public function clearCache(): void
    {
        $this->cache = new LRUCache(1000);
    }
}
