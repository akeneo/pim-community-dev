<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Cursor to iterate on items
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorWithLimitAndStart implements CursorInterface
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

    /** @var string */
    protected $searchAfterIdentifier;

    /** @var array */
    protected $searchAfter = null;

    /** @var int */
    protected $fetchedItemsCount = 0;
    
    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $totalCount;

    /** @var int */
    protected $limit;

    /**
     * @param Client                        $esClient
     * @param CursorableRepositoryInterface $repository
     * @param array                         $esQuery
     * @param string                        $indexType
     * @param int                           $pageSize
     * @param string                        $searchAfterIdentifier
     * @param int                           $limit
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        array $esQuery,
        $indexType,
        $pageSize,
        $searchAfterIdentifier,
        $limit = null
    ) {
        $this->repository = $repository;
        $this->esClient = $esClient;
        $this->esQuery = $esQuery;
        $this->indexType = $indexType;
        $this->pageSize = $pageSize;
        $this->limit = $limit;
        
        $this->items = $this->getNextItems($esQuery);
        $this->searchAfterIdentifier = $searchAfterIdentifier;
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
    private function getNextIdentifiers(array $esQuery)
    {
        $itemsCountToFetch = $this->pageSize;
        if (null !== $this->limit) {
            if (($this->fetchedItemsCount + $itemsCountToFetch) > $this->limit) {
                $itemsCountToFetch = $this->fetchedItemsCount - $this->limit;
            }
            $this->fetchedItemsCount += $itemsCountToFetch;
        }

        $esQuery['size'] = $itemsCountToFetch;

        $sort = ['_uid' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;

        if (null !== $this->searchAfter) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($this->indexType, $esQuery);
        $this->totalCount = $response['hits']['total'];

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
        return $this->totalCount;
    }


    protected function get
}
