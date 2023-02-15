<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;

/**
 * Class which call GetCategoriesByCodeQuery and keep a cache for the results.
 *
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCachedCategoriesByCodesQuery
{
    /** @var array<array-key, array<string, array{code: string, label: string, isLeaf: bool}>> $cachedResults */
    private array $cachedResults = [];

    public function __construct(
        private readonly GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery,
    ) {
    }

    /**
     * @param array<string> $codes
     * @return array<string, array{code: string, label: string, isLeaf: bool}>
     */
    public function fetch(array $codes, string $locale): array
    {
        $this->hydrateCache($codes, $locale);
        return \array_filter($this->cachedResults[$locale], fn ($key): bool => \in_array($key, $codes), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param string[] $codes
     */
    public function hydrateCache(array $codes, string $locale): void
    {
        if (!isset($this->cachedResults[$locale])) {
            $this->cachedResults[$locale] = [];
        }
        $codesNotInCache = \array_diff($codes, \array_keys($this->cachedResults[$locale]));
        if (\count($codesNotInCache) > 0) {
            $newResults = \array_reduce(
                $this->getCategoriesByCodeQuery->execute($codesNotInCache, $locale),
                fn (array $carry, array $item): array => \array_merge($carry, [$item['code'] => $item]),
                [],
            );
            $this->cachedResults[$locale] = \array_merge($this->cachedResults[$locale], $newResults);
        }
    }
}
