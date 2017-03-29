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
        $currentItem = $this->current();

        if (false === next($this->items)) {
            $searchAfterIdentifier = $currentItem->getIdentifier();
            $this->items = $this->getNextItems($this->esQuery, $searchAfterIdentifier);
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
     * @param string|null $searchAfter
     *
     * @return array
     */
    private function getNextItems(array $esQuery, $searchAfter = null)
    {
        $identifiers = $this->getNextIdentifiers($esQuery, $searchAfter);
        if (empty($identifiers)) {
            return [];
        }

        return $this->repository->getItemsFromIdentifiers($identifiers);
    }

    /**
     * Get the next identifiers from elasticsearch query
     *
     * @param array       $esQuery
     * @param string|null $searchAfter
     *
     * @return array
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-search-after.html
     */
    private function getNextIdentifiers(array $esQuery, $searchAfter = null)
    {
        $esQuery['size'] = $this->pageSize;

        $sort = ['_uid' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;

        if (null !== $searchAfter) {
            $esQuery['search_after'] = [$this->indexType . '#' . $searchAfter];
        }

        $response = $this->esClient->search($this->indexType, $esQuery);
        $this->count = $response['hits']['total'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
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
}
