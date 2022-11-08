<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Cache;

use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LRUCacheGetProductUuids implements GetProductUuids, CachedQueryInterface
{
    private LRUCache $cacheByIdentifiers;
    private LRUCache $cacheByUuids;

    public function __construct(private GetProductUuids $getProductUuids)
    {
        $this->cacheByIdentifiers = new LRUCache(1000);
        $this->cacheByUuids = new LRUCache(1000);
    }

    public function fromIdentifier(string $identifier): ?UuidInterface
    {
        return $this->cacheByIdentifiers->getForKey(
            $identifier,
            fn (string $identifier): ?UuidInterface => $this->getProductUuids->fromIdentifier($identifier)
        );
    }

    public function fromIdentifiers(array $identifiers): array
    {
        return $this->cacheByIdentifiers->getForKeys(
            $identifiers,
            fn (array $identifiersNotFound): array => $this->getProductUuids->fromIdentifiers($identifiersNotFound)
        );
    }

    public function fromUuid(UuidInterface $uuid): ?UuidInterface
    {
        return $this->cacheByUuids->getForKey(
            $uuid->toString(),
            fn (string $notCachedUuid): ?UuidInterface => $this->getProductUuids->fromUuid(Uuid::fromString($notCachedUuid))
        );
    }

    public function fromUuids(array $uuids): array
    {
        return $this->cacheByUuids->getForKeys(
            \array_map(static fn (UuidInterface $uuid): string => $uuid->toString(), $uuids),
            fn (array $notCachedUuids): array => $this->getProductUuids->fromUuids(
                \array_map(
                    static fn (string $notCacheUuid): UuidInterface => Uuid::fromString($notCacheUuid),
                    $notCachedUuids
                )
            )
        );
    }

    public function clearCache(): void
    {
        $this->cacheByIdentifiers = new LRUCache(1000);
        $this->cacheByUuids = new LRUCache(1000);
    }
}
