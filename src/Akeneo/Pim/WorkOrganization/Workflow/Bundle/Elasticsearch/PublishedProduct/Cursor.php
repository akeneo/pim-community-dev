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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\PublishedProduct;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

class Cursor extends AbstractCursor implements CursorInterface
{
    private array $searchAfter = [];

    public function __construct(
        Client $esClient,
        PublishedProductRepositoryInterface $publishedProductRepository,
        private array $esQuery,
        private int $pageSize
    ) {
        $this->esClient = $esClient;
        $this->publishedProductRepository = $publishedProductRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        if (false === next($this->items)) {
            $this->position += count($this->items);
            $this->items = $this->getNextItems($this->esQuery);
            reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->searchAfter = [];
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }

    /**
     * @return string[]
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html
     */
    protected function getNextIdentifiers(array $esQuery, int $size = null): array
    {
        $esQuery['size'] = $size ?? $this->pageSize;

        if (0 === $esQuery['size']) {
            return [];
        }

        $sort = ['id' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;
        $esQuery['track_total_hits'] = true;

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($esQuery);
        $this->count = $response['hits']['total']['value'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
        }

        $lastResult = end($response['hits']['hits']);

        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return $identifiers;
    }

    /**
     * Get the next items (hydrated from doctrine repository).
     *
     * PIM-10232: The quick-and-dirty fix here is to always return "pageSize" items (except of course when no more result is found).
     * Before the fix we could return less than the "pageSize" count, when ES and MySQL are de-synchronized (= there
     * is more result in ES than in MySQL).
     * Returning fewer results can cause some UoW issues (c.f. ticket)
     */
    protected function getNextItems(array $esQuery): array
    {
        $pageSize = $this->pageSize;

        $totalItems = [];
        $try = 0;
        do {
            $try++;

            $numberOfIdentifiersToFind = $pageSize - \count($totalItems);
            $identifierResults = $this->getNextIdentifiers($esQuery, $numberOfIdentifiersToFind);
            $newItems = $this->getNextItemsFromIdentifiers($identifierResults);
            $totalItems = \array_merge($totalItems, $newItems);
            if (\count($identifierResults) < $numberOfIdentifiersToFind) {
                // There is no more result, we can stop the loop.
                break;
            }
        } while (\count($totalItems) < $pageSize && $try <= 5);

        return $totalItems;
    }
}
