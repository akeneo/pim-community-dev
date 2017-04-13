<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Cursor to iterate on items
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Cursor implements CursorInterface
{
    /** @var Client */
    protected $esClient;

    /** @var CursorableRepositoryInterface */
    protected $repository;

    /** @var array */
    protected $esQuery;

    /** @var string */
    protected $indexType;

    /** @var array */
    protected $items = [];

    /** @var array */
    protected $searchAfter = [];

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $count;

    /**
     * @param Client                        $esClient
     * @param CursorableRepositoryInterface $repository
     * @param array                         $esQuery
     * @param string                        $indexType
     * @param int                           $pageSize
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        array $esQuery,
        $indexType,
        $pageSize
    ) {
        $this->repository = $repository;
        $this->esClient = $esClient;
        $this->esQuery = $esQuery;
        $this->indexType = $indexType;
        $this->pageSize = $pageSize;

        $this->items = $this->getNextItems($esQuery);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (false === next($this->items)) {
            $this->items = $this->getNextItems($this->esQuery);
            $this->rewind();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->items);
    }

    /**
     * Get the next items (hydrated from doctrine repository)
     *
     * @param array       $esQuery
     *
     * @return array
     */
    private function getNextItems(array $esQuery)
    {
        $identifiers = $this->getNextIdentifiers($esQuery);
        if (empty($identifiers)) {
            return [];
        }

        $hydratedItems = $this->repository->getItemsFromIdentifiers($identifiers);

        $orderedItems = [];

        foreach ($identifiers as $identifier) {
            foreach ($hydratedItems as $hydratedItem) {
                if ($identifier === $hydratedItem->getIdentifier()) {
                    $orderedItems[] = $hydratedItem;
                    break;
                }
            }
        }

        return $orderedItems;
    }

    /**
     * Get the next identifiers from elasticsearch query
     *
     * @param array $esQuery
     *
     * @return array
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-search-after.html
     */
    protected function getNextIdentifiers(array $esQuery)
    {
        $esQuery['size'] = $this->getItemsCountToFetch();

        if (0 === $esQuery['size']) {
            return [];
        }

        $sort = ['_uid' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($this->indexType, $esQuery);
        $this->count = $response['hits']['total'];

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
    public function count()
    {
        return $this->count;
    }

    /**
     * @return int
     */
    protected function getItemsCountToFetch()
    {
        return $this->pageSize;
    }
}
