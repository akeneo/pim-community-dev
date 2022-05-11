<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;

class CategoryIndexer
{
    private const CATEGORY_IDENTIFIER_PREFIX = 'category_';
    private const BATCH_SIZE = 500;

    public function __construct(
        private Client $categoryClient,
        private GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
    }
    
    public function indexFromCategoryIdentifier(string $categoryIdentifier, array $options = []): void
    {
        $this->indexFromCategoryIdentifiers([$categoryIdentifier], $options);
    }

    public function indexFromCategoryIdentifiers(array $categoryIdentifiers, array $options = []): void
    {
        if (empty($categoryIdentifiers)) {
            return;
        }

        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        $chunks = array_chunk($categoryIdentifiers, self::BATCH_SIZE);
        foreach ($chunks as $categoryIdentifiersChunk) {
            $elasticsearchcategoryProjections = $this->getElasticsearchProductProjection->fromProductIdentifiers(
                $categoryIdentifiersChunk
            );

            $normalizedcategoryProjections = (
            static function (iterable $projections): iterable {
                foreach ($projections as $identifier => $projection) {
                    yield $identifier => $projection->toArray();
                }
            }
            )($elasticsearchcategoryProjections);

            $this->categoryClient->bulkIndexes($normalizedcategoryProjections, 'id', $indexRefresh);
        }
    }

    /**
     * Removes the category from the category index.
     *
     * {@inheritdoc}
     */
    public function removeFromCategoryId(int $categoryId): void
    {
        $this->categoryClient->delete(self::CATEGORY_IDENTIFIER_PREFIX . $categoryId);
    }

    /**
     * Removes the categorys from the category index.
     *
     * {@inheritdoc}
     */
    public function removeFromCategoryIds(array $categoryIds): void
    {
        $this->categoryClient->bulkDelete(array_map(
            function ($categoryId) {
                return self::CATEGORY_IDENTIFIER_PREFIX . (string) $categoryId;
            },
            $categoryIds
        ));
    }
}
