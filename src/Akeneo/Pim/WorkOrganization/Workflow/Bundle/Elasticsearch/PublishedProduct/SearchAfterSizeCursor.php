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

class SearchAfterSizeCursor extends AbstractCursor implements CursorInterface
{
    /** @var int */
    protected $fetchedItemsCount;

    /** @var array */
    protected $initialSearchAfter;

    public function __construct(
        Client $esClient,
        PublishedProductRepositoryInterface $publishedProductRepository,
        protected array $esQuery,
        protected array $searchAfter,
        protected int $pageSize,
        protected int $limit,
        protected ?string $searchAfterUniqueKey = null
    ) {
        $this->publishedProductRepository = $publishedProductRepository;
        $this->esClient = $esClient;
        $this->initialSearchAfter = $this->searchAfter;
        $this->fetchedItemsCount = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (false === next($this->items)) {
            $this->fetchedItemsCount += count($this->items);
            $this->items = $this->getNextItems($this->esQuery);
            reset($this->items);
        }
    }

    /**
     * Get the next items (hydrated from doctrine repository).
     *
     * PIM-102132: The quick-and-dirty fix here is to always return "pageSize" items (except of course when no more result is found).
     * Before the fix we could return less than the "pageSize" count, when ES and MySQL are de-synchronized (= there
     * is more result in ES than in MySQL).
     * Returning fewer results can cause some UoW issues (c.f. ticket)
     */
    protected function getNextItems(array $esQuery): array
    {
        $pageSize = min($this->pageSize, $this->limit);

        $totalItems = [];
        $try = 0;
        do {
            $try++;

            $numberOfIdentifiersToFind = $pageSize - \count($totalItems);
            $identifiers = $this->getNextIdentifiers($esQuery, $numberOfIdentifiersToFind);
            $totalItems = \array_merge($totalItems, $this->getNextItemsFromIdentifiers($identifiers));
            if (\count($identifiers) < $numberOfIdentifiersToFind) {
                // There is no more result, we can stop the loop.
                break;
            }
        } while (\count($totalItems) < $pageSize && $try <= 5);

        return $totalItems;
    }

    /**
     * {@inheritdoc}
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html
     */
    protected function getNextIdentifiers(array $esQuery, int $size = null): array
    {
        $pageSize = $size ?? $this->pageSize;
        $size = $this->limit > $pageSize ? $pageSize : $this->limit;
        if ($this->fetchedItemsCount + $size > $this->limit) {
            $size = $this->limit - $this->fetchedItemsCount;
        }
        $esQuery['track_total_hits'] = true;
        $esQuery['size'] = $size;

        if (0 === $esQuery['size']) {
            return [];
        }

        $sort = ['id' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;

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
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->searchAfter = $this->initialSearchAfter;
        if (null !== $this->searchAfterUniqueKey) {
            $this->searchAfter[] = $this->searchAfterUniqueKey;
        }

        $this->fetchedItemsCount = 0;
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }
}
