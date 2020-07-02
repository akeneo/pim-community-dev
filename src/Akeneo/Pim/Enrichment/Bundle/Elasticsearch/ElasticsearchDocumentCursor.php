<?php


declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ElasticsearchDocumentCursor implements CursorInterface
{
    /** @var Client */
    protected $esClient;

    /** @var array */
    protected $esQuery;

    /** @var array */
    protected $items;

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $count;

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
        array $esQuery,
        array $searchAfter = [],
        $pageSize,
        $limit,
        $searchAfterUniqueKey = null
    ) {
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
    public function current()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        if (empty($this->items)) {
            return null;
        }

        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return $this->count;
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

    protected function getNextItems(array $esQuery)
    {
        $size = $this->limit > $this->pageSize ? $this->pageSize : $this->limit;
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

        $items = [];
        foreach ($response['hits']['hits'] as $hit) {
            $items[] = $hit['_source'];
        }

        $lastResult = end($response['hits']['hits']);

        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return $items;
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
