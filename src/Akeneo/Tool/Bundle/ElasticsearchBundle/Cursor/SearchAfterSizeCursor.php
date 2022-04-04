<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Bounded cursor to iterate over items where a start and a limit are defined.
 * Internally, this is implemented with the search after pagination.
 * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html}
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAfterSizeCursor extends AbstractCursor implements CursorInterface
{
    /** @var string|null */
    protected $searchAfterUniqueKey;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $fetchedItemsCount;

    /** @var array */
    protected $searchAfter;

    /** @var array */
    protected $initialSearchAfter;

    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        array $esQuery,
        array $searchAfter,
        int $pageSize,
        int $limit,
        ?string $searchAfterUniqueKey = null
    ) {
        $this->repository = $repository;
        $this->esClient = $esClient;
        $this->esQuery = $esQuery;
        $this->pageSize = $pageSize;
        $this->limit = $limit;
        $this->searchAfter = $searchAfter;
        $this->initialSearchAfter = $this->searchAfter;
        $this->searchAfterUniqueKey = $searchAfterUniqueKey;
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
    protected function getNextIdentifiers(array $esQuery, int $size = null)
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

        $sort = ['_id' => 'asc'];

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
            array_push($this->searchAfter, $this->searchAfterUniqueKey);
        }

        $this->fetchedItemsCount = 0;
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }
}
