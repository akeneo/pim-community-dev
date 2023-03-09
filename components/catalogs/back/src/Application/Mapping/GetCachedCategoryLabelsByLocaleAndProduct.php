<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductCategoryCodesQuery;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\MessageBusInterface;

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
        private readonly GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery,
        private readonly MessageBusInterface $queryBus,
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

        $formattedResponse = [];
        foreach ($productUuids as $productUuid) {
            $formattedResponse[$productUuid] = [];
            foreach ($locales as $locale) {
                $formattedResponse[$productUuid][$locale] = [];
                foreach ($this->categoryCodesByProduct[$productUuid] as $categoryCode) {
                    $formattedResponse[$productUuid][$locale][] = $this->categoryLabelsByLocale[$locale][$categoryCode];
                }
            }
        }
        return $formattedResponse;
    }

    /**
     * @param string[] $productUuids
     * @param string[] $locales
     */
    public function hydrateCache(array $productUuids, array $locales): void
    {
        $productUuidsNotInCache = \array_diff($productUuids, \array_keys($this->categoryCodesByProduct));
        if (\count($productUuidsNotInCache) === 0) {
            return;
        }
        $envelope = $this->queryBus->dispatch(
            new GetProductCategoryCodesQuery(
                \array_map(fn ($strUuid): UuidInterface => Uuid::fromString($strUuid), $productUuidsNotInCache)
            )
        );
        $categoriesByProductNotInCache = $envelope->last(HandledStamp::class)?->getResult();

        $this->categoryCodesByProduct = \array_merge(
            $this->categoryCodesByProduct,
            $categoriesByProductNotInCache,
        );

        $categoryCodesForGivenProducts = [];
        foreach ($this->categoryCodesByProduct as $productUuid => $categories) {
            if (\in_array($productUuid, $productUuids)) {
                $categoryCodesForGivenProducts = \array_unique(\array_merge($categoryCodesForGivenProducts, $categories));
            }
        }

        foreach ($locales as $locale) {
            $categoryCodesToLocalize = \array_diff(
                $categoryCodesForGivenProducts,
                \array_keys($this->categoryLabelsByLocale[$locale] ?? []),
            );
            if (\count($categoryCodesToLocalize) > 0) {
                $categories = $this->getCategoriesByCodeQuery->execute($categoryCodesToLocalize, $locale);
                foreach ($categories as $category) {
                    $this->categoryLabelsByLocale[$locale][$category['code']] = $category['label'];
                }
            }
        }
    }
}
