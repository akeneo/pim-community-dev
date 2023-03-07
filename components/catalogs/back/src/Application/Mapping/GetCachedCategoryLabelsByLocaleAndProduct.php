<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetCategoryCodesByProductQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCachedCategoryLabelsByLocaleAndProduct
{
    /** @var array<string, string[]> $categoryCodesByProduct */
    private array $categoryCodesByProduct = [];

    /** @var array<string, array<string, string>> $categoryLabelsByLocale */
    private array $categoryLabelsByLocale = [];

    public function __construct(
        private readonly GetCategoryCodesByProductQueryInterface $getCategoryCodesByProductQuery,
        private readonly GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery,
    ) {
    }

    /**
     * @param string[] $productUuids
     * @param string[] $locales
     * @return array<string, array<string, string[]>>
     */
    public function fetch(array $productUuids, array $locales): array
    {
        $this->hydrateCache($productUuids, $locales);

        /** @var array<string, array<string, string[]>> */
        $results = \array_reduce(
            $productUuids,
            fn (array $byProductUuid, string $productUuid) => \array_merge(
                $byProductUuid,
                [$productUuid => \array_reduce(
                    $locales,
                    fn (array $byLocale, string $locale) => \array_merge(
                        $byLocale,
                        [$locale => \array_filter(
                            $this->categoryLabelsByLocale[$locale] ?? [],
                            fn (string $code): bool => \in_array($code, $this->categoryCodesByProduct[$productUuid]),
                            ARRAY_FILTER_USE_KEY,
                        )],
                    ),
                    [],
                )],
            ),
            [],
        );
        return $results;
    }

    /**
     * @param string[] $productUuids
     * @param string[] $locales
     */
    public function hydrateCache(array $productUuids, array $locales): void
    {
        $productUuidsNotInCache = \array_diff($productUuids, \array_keys($this->categoryCodesByProduct));

        /** @var array<string, string[]> */
        $newCategoryCodesByProduct = \array_reduce(
            $this->getCategoryCodesByProductQuery->execute($productUuidsNotInCache),
            function (array $carry, array $result): array {
                /** @var string */
                $uuid = $result['product_uuid'];
                /** @var string */
                $codes = $result['category_codes'];
                return \array_merge($carry, [$uuid => \json_decode($codes, null, 512, JSON_THROW_ON_ERROR)]);
            },
            [],
        );
        $this->categoryCodesByProduct = \array_merge(
            $this->categoryCodesByProduct,
            $newCategoryCodesByProduct,
        );

        $categoryCodes = \array_reduce(
            \array_filter($this->categoryCodesByProduct, fn ($productUuid): bool => \in_array($productUuid, $productUuids), ARRAY_FILTER_USE_KEY),
            fn (array $carry, array $categories): array => \array_unique(\array_merge($carry, $categories)),
            [],
        );

        foreach ($locales as $locale) {
            /** @var string[] */
            $categoryCodesToLocalize = \array_diff($categoryCodes, \array_keys($this->categoryLabelsByLocale[$locale] ?? []));
            if (\count($categoryCodesToLocalize) > 0) {
                /** @var array<string, string> */
                $newCategoryLabelsByLocale = \array_reduce(
                    $this->getCategoriesByCodeQuery->execute($categoryCodesToLocalize, $locale),
                    /** @param array{code: string, label: string, isLeaf: bool} $category */
                    fn (array $carry, array $category) => \array_merge(
                        $carry,
                        [$category['code'] => $category['label']],
                    ),
                    [],
                );
                $this->categoryLabelsByLocale[$locale] = \array_merge(
                    $this->categoryLabelsByLocale[$locale] ?? [],
                    $newCategoryLabelsByLocale,
                );
            }
        }
    }
}
