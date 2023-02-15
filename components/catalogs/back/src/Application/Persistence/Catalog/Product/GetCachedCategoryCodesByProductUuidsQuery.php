<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetCategoryCodesByProductUuids;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class which call GetCategoryCodesByProductUuids and keep a cache for the results.
 *
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCachedCategoryCodesByProductUuidsQuery
{
    /** @var array<string, array<string>> $cachedResults */
    private array $cachedResults = [];

    public function __construct(
        private readonly GetCategoryCodesByProductUuids $getCategoryCodesByProductUuids,
    ) {
    }

    /**
     * @param array<UuidInterface> $uuids
     * @return array<string, array<string>>
     */
    public function fetch(array $uuids): array
    {
        $this->hydrateCache($uuids);
        return \array_filter($this->cachedResults, fn ($key): bool => \in_array($key, $uuids), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<UuidInterface> $uuids
     */
    public function hydrateCache(array $uuids): void
    {
        $uuidsStrings = \array_map(function ($uuid): string {
            /** @var string $serialized */
            $serialized = $uuid->serialize();
            return $serialized;
        }, $uuids);
        $uuidsNotInCache = \array_diff($uuidsStrings, \array_keys($this->cachedResults));

        if (\count($uuidsNotInCache) > 0) {
            $newResults = $this->getCategoryCodesByProductUuids->fetchCategoryCodes(
                \array_map(
                    fn (string $uuidString): UuidInterface => Uuid::fromString($uuidString),
                    $uuidsNotInCache,
                ),
            );
            $this->cachedResults = \array_merge($this->cachedResults, $newResults);
        }
    }
}
