<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Category\GetProductCategoriesLabelsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\WarmupAwareQueryInterface;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductCategoryCodesQuery;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductCategoriesLabelsQuery implements GetProductCategoriesLabelsQueryInterface, WarmupAwareQueryInterface
{
    /** @var array<string, string[]> $categoryCodesByProduct */
    private array $categoryCodesByProduct = [];

    /** @var array<string, array<string, string>> $categoryLabelsByLocale */
    private array $categoryLabelsByLocale = [];

    public function __construct(
        private readonly GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery,
        private readonly MessageBusInterface $enrichmentQueryBus,
    ) {
    }


    /**
     * @inheritDoc
     */
    public function execute(string $productUuid, string $locale): array
    {
        $this->warmup([
            'productUuids' => [$productUuid],
            'locales' => [$locale],
        ]);

        return \array_map(
            fn (string $categoryCode): string => $this->categoryLabelsByLocale[$locale][$categoryCode] ?? \sprintf('[%s]', $categoryCode),
            $this->categoryCodesByProduct[$productUuid] ?? [],
        );
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param array{
     *     productUuids?: array<string>,
     *     locales?: array<string>,
     * } $options
     */
    public function warmup(array $options = []): void
    {
        if (isset($options['productUuids'])) {
            $this->warmupProductCategories($options['productUuids']);

            if (isset($options['locales'])) {
                $this->warmupCategoryLabels($options['productUuids'], $options['locales']);
            }
        }
    }

    /**
     * @param string[] $productUuids
     */
    private function warmupProductCategories(array $productUuids): void
    {
        $productUuidsNotInCache = \array_diff($productUuids, \array_keys($this->categoryCodesByProduct));
        if ([] === $productUuidsNotInCache) {
            return;
        }

        $envelope = $this->enrichmentQueryBus->dispatch(
            new GetProductCategoryCodesQuery(
                \array_map(static fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $productUuidsNotInCache),
            ),
        );

        /** @var HandledStamp|null $queryResponse */
        $queryResponse = $envelope->last(HandledStamp::class);

        if (null === $queryResponse) {
            throw new \LogicException('The query must return a result.');
        }

        /** @var array<string, array<string>> $categoriesByProductNotInCache */
        $categoriesByProductNotInCache = $queryResponse->getResult();

        $this->categoryCodesByProduct = \array_merge(
            $this->categoryCodesByProduct,
            $categoriesByProductNotInCache,
        );
    }

    /**
     * @param string[] $productUuids
     * @param string[] $locales
     */
    private function warmupCategoryLabels(array $productUuids, array $locales): void
    {
        $categoryCodesForGivenProducts = [];
        foreach ($productUuids as $productUuid) {
            $categoryCodesForGivenProducts = \array_unique(\array_merge(
                $categoryCodesForGivenProducts,
                $this->categoryCodesByProduct[$productUuid] ?? [],
            ));
        }

        foreach ($locales as $locale) {
            $categoryCodesToLocalize = \array_diff(
                $categoryCodesForGivenProducts,
                \array_keys($this->categoryLabelsByLocale[$locale] ?? []),
            );

            if ([] !== $categoryCodesToLocalize) {
                $categories = $this->getCategoriesByCodeQuery->execute($categoryCodesToLocalize, $locale);
                foreach ($categories as $category) {
                    $this->categoryLabelsByLocale[$locale][$category['code']] = $category['label'];
                }
            }
        }
    }
}
