<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Bounded cursor to iterate over items where a start and a limit are defined.
 * Internally, this is implemented with the from/size pagination.
 * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-from-size.html}
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FromSizeCursor extends AbstractCursor implements CursorInterface
{
    /** @var int */
    protected $initialFrom;

    /** @var int */
    protected $from;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $to;

    /** @var int */
    protected $fetchedItemsCount;

    /**
     * @param Client                        $esClient
     * @param CursorableRepositoryInterface $repository
     * @param array                         $esQuery
     * @param int                           $pageSize
     * @param int                           $limit
     * @param int                           $from
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        array $esQuery,
        $pageSize,
        $limit,
        $from = 0
    ) {
        $this->esClient = $esClient;
        $this->repository = $repository;
        $this->esQuery = $esQuery;
        $this->pageSize = $pageSize;
        $this->limit = $limit;
        $this->from = $from;
        $this->initialFrom = $from;
        $this->to = $this->from + $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (false === next($this->items)) {
            $this->from += count($this->items);
            $this->items = $this->getNextItems($this->esQuery);
            reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     *
     * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-from-size.html}
     */
    protected function getNextIdentifiers(array $esQuery)
    {
        $size = ($this->to - $this->from) > $this->pageSize ? $this->pageSize : ($this->to - $this->from);
        $esQuery['size'] = $size;

        if (0 === $esQuery['size']) {
            return [];
        }

        $sort = ['_id' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;
        $esQuery['from'] = $this->from;
        $esQuery['track_total_hits'] = true;

        $response = $this->esClient->search($esQuery);
        $this->count = $response['hits']['total']['value'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
        }

        return $identifiers;
    }


    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->from = $this->initialFrom;
        $this->to = $this->from + $this->limit;
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }
}
